<?php
class conexao
{
    private $db_host;
    private $db_user;
    private $db_pass;
    private $db_name;
    private $db_port;
    private $link = null;
    private $lastError = '';

    public function __construct()
    {
        // este arquivo está na mesma pasta deste .class.php
        require  'config.php';

        $this->db_host = $db_host ?? '127.0.0.1';
        $this->db_user = $db_user ?? 'root';
        $this->db_pass = $db_pass ?? '';
        $this->db_name = $db_name ?? 'paixao';
        $this->db_port = $db_port ?? 3306;
    }

    public function connect()
    {
        if ($this->link instanceof mysqli) {
            return $this->link;
        }

        // tenta com host informado
        $this->link = @mysqli_connect(
            $this->db_host,
            $this->db_user,
            $this->db_pass,
            $this->db_name,
            $this->db_port
        );

        // se falhar e host for 'localhost', tenta '127.0.0.1'
        if (!$this->link && $this->db_host === 'localhost') {
            $this->link = @mysqli_connect(
                '127.0.0.1',
                $this->db_user,
                $this->db_pass,
                $this->db_name,
                $this->db_port
            );
        }

        if (!$this->link) {
            $this->lastError = mysqli_connect_error();
            return false;
        }

        @mysqli_set_charset($this->link, 'utf8mb4');
        return $this->link;
    }

    public function getError(): string
    {
        return $this->lastError;
    }

    public function disconnect()
    {
        if ($this->link instanceof mysqli) {
            @mysqli_close($this->link);
            $this->link = null;
            return true;
        }
        return false;
    }
}
