<?php

namespace CD\App\Cron;

use CD\Models\Team;
use CD\Models\Teams;
use Exception;

class Cron
{
    private Teams $model;
    private string $endpoint = 'https://game-api.axie.technology/api/v1/';
    private array $ronin_addresses = [];
    private array $addresses = [];
//    private array $balances = [];
//    private array $team_total_slp = [];
//    private array $next_claims = [];
    private array $has_claims = [];
    private array $db_team_data = [];
    private $api_data;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->model = new Teams();
        try {
            if (!is_cli()) {
                echo 'This page does not exist' . PHP_EOL;
                http_response_code(404);
                exit;
            }
        } catch (\PDOException | Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $this->ronin_addresses = $this->model->getRoninAddresses();
        $addresses = [];
//        $balances = [];
//        $next_claims = [];
//        $tts = [];
        $db_team_data = [];
        foreach ($this->ronin_addresses as $ronin_address) {
            $address = $ronin_address['ronin'];
            $id = $ronin_address['id'];
            preg_match('/([a-z0-9]{40})/', $address, $matches);
            $addresses[$id] = '0x' . $matches[0];
//            $balances['0x' . $matches[0]] = $ronin_address['latest_balance'];
//            $next_claims['0x' . $matches[0]] = strtotime($ronin_address['next_claim']);
//            $tts['0x' .  $matches[0]] = $ronin_address['total_slp'];
            $db_team_data['0x' .  $matches[0]]['latest_balance'] = $ronin_address['latest_balance'];
            $db_team_data['0x' .  $matches[0]]['next_claim'] = strtotime($ronin_address['next_claim']);
            $db_team_data['0x' .  $matches[0]]['total_slp'] = $ronin_address['total_slp'];
            $db_team_data['0x' .  $matches[0]]['royalty'] = $ronin_address['royalty'];
        }
        $this->addresses = $addresses;
//        $this->balances = $balances;
//        $this->next_claims = $next_claims;
//        $this->team_total_slp = $tts;
        $this->db_team_data = $db_team_data;
        if ($data = file_get_contents($this->endpoint . join(',', array_values($addresses)))) {
            $this->api_data = json_decode($data, true);
        } else {
            throw new Exception('Cannot access API endpoint', 503);
        }
    }

    /**
     * @throws Exception
     */
    public function update_teams_data(): void
    {
//        echo 'update_teams_data' . PHP_EOL;
        foreach ($this->addresses as $id => $ronin) {
            $team = $this->api_data[$ronin];
            $new_values['AxieTeamMMR'] = $team['mmr'];
            $new_values['AxieTeamRank'] = $team['rank'];
            $new_values['AxieTeamCurrentSLPBalance'] = $team['total_slp'];
            if ($team['next_claim'] > 1209600) {
                $next = date('Y-m-d H:i:s', $team['next_claim']);
            } else {
                $next = null;
            }
            if ($team['last_claim'] > 0) {
                $last = date('Y-m-d H:i:s', $team['last_claim']);
            } else {
                $last = null;
            }
            $new_values['AxieTeamNextSLPClaim'] = $next;
            $new_values['AxieTeamLastSLPClaim'] = $last;
            $new_values['AxieTeamDateLastModified'] = date('Y-m-d H:i:s');
            if (!$this->model->updateTeamData($id, $new_values)) {
                throw new Exception('Failed to update team data', 500);
            }
            if ($team['next_claim'] !== 1209600 && $team['next_claim'] > $this->db_team_data[$ronin]['next_claim']) {
                $this->has_claims[$id] = $ronin;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function update_daily_slp()
    {
//        echo 'update_daily_slp' . PHP_EOL;
        foreach ($this->addresses as $id => $ronin) {
            $team = $this->api_data[$ronin];
            if ($team['total_slp'] > $this->db_team_data[$ronin]['latest_balance']) {
                $added = $team['total_slp'] - $this->db_team_data[$ronin]['latest_balance'];
                if (!$this->model->updateDailySLP($id, $added)) {
                    throw new Exception('Failed to fetch daily slp', 500);
                }
            }
        }
    }

    public function update_team_claims()
    {
        if ($this->has_claims) {
//            echo 'update_team_claims' . PHP_EOL;
            $team = new Teams();
            $error = false;
            foreach ($this->has_claims as $id => $team_ronin) {
                $t = $this->db_team_data[$team_ronin];
                $api = $this->api_data[$team_ronin];
                $last_bal = $t['total_slp'];
                $updated = $api['lifetime_slp'];
                $claimed = round($updated - $last_bal, 2);
                $payout_date = date('Y-m-d H:i:s', $api['last_claim']);
                $manager_share = round(($claimed * ($t['royalty'] / 100)), 2);
                $cd = round($claimed - $manager_share, 2);
                $at_id = 0;
                if ($fortnight_id = $this->model->newFortnight($claimed, $payout_date)) {
                    if ($at_id = $this->model->newTeamPayout($id, $fortnight_id, $manager_share, $payout_date, $t['royalty'])) {
                        if (!$this->model->newCDPayout($at_id, $cd, $payout_date, 40)) {
                            $error = 'Failed to update Chaindrawer payout data';
                        }
                    } else {
                        $error = 'Failed to update Team and Chaindrawer payout data';
                    }
                } else {
                    $error = 'Failed to update Fortnight, Team, and Chaindrawer payout data';
                }
                if ($error === false) {
                    $managers = $team->getTeamByID($id)->getTeamManagers();
                    foreach ($managers as $manager) {
                        $amount = round(($manager_share * ($manager->OwnershipRate) / 100), 2);
                        if ($manager->addToBalance($amount)) {
                            if (!$this->model->newManagerPayout($amount, $manager->getManagerAccountID(), $manager->ManagerAccountCurrentSLPBalance, $payout_date, $manager->OwnershipRate, $at_id)) {
                                $error = 'Failed to insert manager payout';
                                break;
                            }
                        } else {
                            $error = 'Failed to update manager balance.';
                            break;
                        }
                    }
                }
                if ($error) {
                    echo $error;
                    return;
                }
                if (!$team->updateTeamLifetimeSLP($id, $claimed)) {
                    echo 'Failed updating team lifetime balance';
                    return;
                }
            }
        }
    }

    public function test()
    {
        $team = new Teams();
        $id = 3;
        $managers = $team->getTeamByID($id)->getTeamManagers();
        $claimed = 4252.8;
        $manager_share = 1594.8;
        $payout_date = '2021-10-02 00:00:00';
        $error = '';
        foreach ($managers as $manager) {
            $amount = round(($manager_share * ($manager->OwnershipRate) / 100), 2);
            if ($manager->addToBalance($amount)) {
                if (!$this->model->newManagerPayout($amount, $manager->getManagerAccountID(), $manager->ManagerAccountCurrentSLPBalance, $payout_date, $manager->OwnershipRate, 13)) {
                    $error = 'Failed to insert manager payout';
                    break;
                }
            } else {
                $error = 'Failed to update manager balance.';
                break;
            }
        }
        if ($error) {
            echo $error;
            return;
        } else if (!$team->updateTeamLifetimeSLP($id, $claimed)) {
            echo 'Failed updating team lifetime balance';
            return;
        } else {
            echo 'updated!';
        }

    }

}