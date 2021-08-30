<?php

namespace CD\Core;

use Exception;

class CronManager
{
    private $connection;
    private string $path;
    private string $handle;
    private string $cron_file;

    function __construct($host = null, $port = null, $username = null, $password = null)
    {
        $path_length = strpos(__FILE__, '/');
        $this->path = substr(__FILE__, 0, $path_length) . '/';
        $this->handle = 'crontab.txt';
        $this->cron_file = "{$this->path}{$this->handle}";
        try {
            if (is_null($host) || is_null($port) || is_null($username) || is_null($password)) throw new Exception('Please specify the host, port, username and password!');
            $this->connection = @ssh2_connect($host, $port);
            if (!$this->connection) {
                throw new Exception('The SSH2 connection could not be established.');
            }

            $authentication = @ssh2_auth_password($this->connection, $username, $password);
            if (!$authentication) {
                throw new Exception("Could not authenticate '{$username}' using password: '{$password}'.");
            }

        } catch (Exception $e) {
            $this->errorMessage($e->getMessage());
        }
    }

    public function exec(): self
    {
        $argument_count = func_num_args();
        try {
            if (!$argument_count) {
                throw new Exception("There is nothing to execute, no arguments specified.");
            }
            $arguments = func_get_args();
            $command_string = ($argument_count > 1) ? implode('&&', $arguments) : $arguments[0];

            $stream = @ssh2_exec($this->connection, $command_string);
            if (!$stream) {
                throw new Exception("Unable to execute the specified commands:<br>$command_string");
            }
        } catch (Exception $e) {
            $this->errorMessage($e->getMessage());
        }
        return $this;
    }

    public function writeToFile(?string $path = null, ?string $handle = null): self
    {
        if (!$this->crontabFileExists()) {
            $this->handle = (is_null($handle)) ? $this->handle : $handle;
            $this->path = (is_null($path)) ? $this->path : $path;
            $this->cron_file = "{$this->path}{$this->handle}";

            $init_cron = "crontab -l > $this->cron_file && [-f $this->cron_file] || > $this->cron_file";
            $this->exec($init_cron);
        }
        return $this;
    }

    public function removeFile(): self
    {
        if ($this->crontabFileExists()) $this->exec("rm {$this->cron_file}");
        return $this;
    }

    public function appendCronjob($cron_jobs = null): self
    {
        if (is_null($cron_jobs)) {
            $this->errorMessage("Nothing to append! Please specify a cron job or an array of cron jobs.");
        }
        $cron_file = "echo '";
        $cron_file .= (is_array($cron_jobs)) ? implode("\n", $cron_jobs) : $cron_jobs;
        $cron_file .= "' >> $this->cron_file";
        $install_cron = "crontab $this->cron_file";
        $this->writeToFile()->exec($cron_file, $install_cron)->removeFile();
        return $this;
    }

    public function removeCronjob(?string $cron_jobs = null): self
    {
        if (is_null($cron_jobs)) {
            $this->errorMessage("Nothing to remove! Please specify a cron job or an array of cron jobs.");
        }
        $this->writeToFile();
        $cron_array = file($this->cron_file, FILE_IGNORE_NEW_LINES);

        if (empty($cron_array)) {
            $this->errorMessage("Nothing to remove! The cronTab is already empty.");
        }
        $original_count = count($cron_array);
        if (is_array($cron_jobs)) {
            foreach ($cron_jobs as $cron_regex) {
                $cron_array = preg_grep($cron_regex, $cron_array, PREG_GREP_INVERT);
            }
        } else {
            $cron_array = preg_grep($cron_jobs, $cron_array, PREG_GREP_INVERT);
        }
        return ($original_count === count($cron_array)) ? $this->removeFile() : $this->removeCrontab()->appendCronjob($cron_array);
    }

    public function removeCrontab(): self
    {
        $this->exec("crontab -r")->removeFile();
        return $this;
    }

    private function crontabFileExists(): bool
    {
        return file_exists($this->cron_file);
    }

    private function errorMessage(string $error)
    {
        die("<pre style='color:#EE2711'>ERROR: $error</pre>");
    }
}