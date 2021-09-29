<?php

namespace CD\App\Cron;

use CD\Models\Teams;
use Exception;

class Cron
{
    private Teams $model;
    private string $endpoint = 'https://game-api.axie.technology/api/v1/';
    private array $ronin_addresses = [];
    private array $addresses = [];
    private array $balances = [];
    private array $next_claims = [];
    private array $has_claims = [];
    private $data;

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
        $balances = [];
        $next_claims = [];
        foreach ($this->ronin_addresses as $ronin_address) {
            $address = $ronin_address['ronin'];
            $id = $ronin_address['id'];
            preg_match('/([a-z0-9]{40})/', $address, $matches);
            $addresses[$id] = '0x' . $matches[0];
            $balances['0x' . $matches[0]] = $ronin_address['latest_balance'];
            $next_claims['0x' . $matches[0]] = $ronin_address['next_claim'];
        }
        $this->balances = $balances;
        $this->addresses = $addresses;
        $this->next_claims = $next_claims;
        if ($data = file_get_contents($this->endpoint . join(',', array_values($addresses)))) {
            $this->data = json_decode($data, true);
        } else {
            throw new Exception('Cannot access API endpoint', 503);
        }
    }

    /**
     * @throws Exception
     */
    public function update_teams_data(): void
    {
        foreach ($this->addresses as $id => $ronin) {
            $team = $this->data[$ronin];
            $new_values['AxieTeamMMR'] = $team['mmr'];
            $new_values['AxieTeamRank'] = $team['rank'];
            $new_values['AxieTeamCurrentSLPBalance'] = $team['total_slp'];
            $new_values['AxieTeamNextSLPClaim'] = date('Y-m-d H:i:s', $team['next_claim']);
            $new_values['AxieTeamLastSLPClaim'] = date('Y-m-d H:i:s', $team['last_claim']);
            $new_values['AxieTeamDateLastModified'] = date('Y-m-d H:i:s');
            if (!$this->model->updateTeamData($id, $new_values)) {
                throw new Exception('Failed to update team data', 500);
            }
            if ($team['next_claim'] > $this->next_claims[$ronin]) {
                $this->has_claims[] = $ronin;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function update_daily_slp()
    {
        foreach ($this->addresses as $id => $ronin) {
            $team = $this->data[$ronin];
            if ($team['total_slp'] > $this->balances[$ronin]) {
                $added = $team['total_slp'] - $this->balances[$ronin];
                if (!$this->model->updateDailySLP($id, $added)) {
                    throw new Exception('Failed to fetch daily slp', 500);
                }
            }
        }
    }

    public function update_team_claims()
    {
        if ($this->has_claims) {
            // distribute
            echo '';
        }
    }

}