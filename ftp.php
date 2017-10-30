<?php

namespace BetterFTP;


class FTP
{
    /**
     *
     * @var string
     */
    private $ftp_server;

    /**
     *
     * @var int
     */
    private $ftp_server_port;

    /**
     *
     * @var string
     */
    private $ftp_username;

    /**
     *
     * @var string
     */
    private $ftp_password;

    /**
     *
     * @var Resource
     */
    private $ftp;

    /**
     *
     * @var Resource
     */
    private $ftp_data;

    /**
     *
     * @var boolean
     */
    private $passive_mode = false;

    /**
     *
     * @var array  of strings
     */
    private $messages = [];

    /**
     * Calculates the port for passive connection
     *
     * @param int $p1
     * @param int $p2
     * @return int
     */
    private function calculatePassivePort(int $p1, int $p2): int
    {
        return intval($p1*256+$p2);
    }

    /**
     * Get the passive connection parameters, returns array with ip address and
     * port
     *
     * @param string $response
     * @return array
     */
    private function passiveConnectionParameters(string $response): array
    {
        $matches = [];
        preg_match('/\(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\,([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\,([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\,([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\,([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\,([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\)/', $response, $matches);

        $connection_parameters = [];
        if (count($matches) === 7) {
            $connection_parameters = [
                'ip' => $matches[1].'.'.$matches[2].'.'.$matches[3].'.'.$matches[4],
                'port' => $this->calculatePassivePort($matches[5], $matches[6]),
            ];
        }

        return $connection_parameters;
    }

    /**
     *
     * @param string $ftp_server
     * @param int $ftp_server_port
     * @param string $ftp_username
     * @param string $ftp_password
     * @param bool $persistent
     */
    public function __construct(string $ftp_server, int $ftp_server_port = 21, string $ftp_username = 'anonymous', string $ftp_password = 'test@example.com', bool $persistent = false)
    {
        $this->ftp_server = $ftp_server;
        $this->ftp_server_port = $ftp_server_port;
        $this->ftp_username = $ftp_username;
        $this->ftp_password = $ftp_password;

        if ($persistent) {
            $this->ftp = pfsockopen($this->ftp_server, $this->ftp_server_port, $errno, $errstr);
        } else {
            $this->ftp = fsockopen($this->ftp_server, $this->ftp_server_port, $errno, $errstr);
        }

        if (is_resource($this->ftp)) {

            $this->messages[] = fread($this->ftp, 1024);
            fwrite($this->ftp, 'USER '.$this->ftp_username.PHP_EOL);
            $this->messages[] =  fread($this->ftp, 1024);
            fwrite($this->ftp, 'PASS '.$this->ftp_password.PHP_EOL);
            $this->messages[] = fread($this->ftp, 1024);

        }

    }

    /**
     * Enables passive mode
     */
    public function startPassiveMode()
    {
        fwrite($this->ftp, 'PASV'.PHP_EOL);
        $this->messages[] = $response = fread($this->ftp, 1024);

        $connection_parameters = $this->passiveConnectionParameters($response);
        $this->ftp_data = fsockopen($connection_parameters['ip'], $connection_parameters['port'], $errno, $errstr);

        $this->passive_mode = true;
    }

    /**
     *
     * @param type $directory
     * @return string
     */
    public function listFiles($directory = '.'): string
    {
        fwrite($this->ftp, 'LIST '.$directory.PHP_EOL);
        if ($this->passive_mode) {
            $response = fread($this->ftp_data, 1024);
        } else {
            $response = fread($this->ftp, 1024);
        }
        $this->messages[] = $response;

        return $response;
    }

    /**
     * Disonnect from the server, does not close the connection
     */
    public function disconnect()
    {
        fwrite($this->ftp, 'QUIT'.PHP_EOL);
        $this->messages[] = fread($this->ftp, 1024);
    }


    public function __destruct() {
        fwrite($this->ftp, 'QUIT'.PHP_EOL);
        fclose($this->ftp);
        fclose($this->ftp_data);
    }

    /**
     * Get the collection of server messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }


}

$ftp = new FTP('ftp.rz.uni-wuerzburg.de');
$ftp->startPassiveMode();
$ftp->listFiles();
$ftp->disconnect();

var_dump($ftp->getMessages());
