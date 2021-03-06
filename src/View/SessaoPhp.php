<?php

namespace Uspdev\Votacao\View;

// classe para manipular $_SESSION
class sessaoPhp
{
    const session_lifetime = 21600; // 6*60*60

    public static function start()
    {
        //https://stackoverflow.com/questions/5238136/increase-php-session-time
        # Session lifetime of 3 hours
        ini_set('session.gc_maxlifetime', SELF::session_lifetime);

        # Enable session garbage collection with a 1% chance of
        # running on each session_start()
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);

        # Our own session save path; it must be outside the
        # default system save path so Debian's cron job doesn't
        # try to clean it up. The web server daemon must have
        # read/write permissions to this directory.
        session_save_path(LOCAL . '/sessions');

        session_set_cookie_params(SELF::session_lifetime);
        session_start();
    }

    public static function destroy()
    {
        unset($_SESSION);
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function verificaSessao($perfil)
    {
        if (isset($_SESSION[$perfil])) {
            $p = $_SESSION[$perfil];

            $hash = $_SESSION['hash'];
            if ($perfil == 'votacao') {
                $token = [];
                if (isset($p['aberta'])) {
                    $token[] = $p['aberta']['token'];
                }
                if (isset($p['fechada'])) {
                    $token[] = $p['fechada']['token'];
                }
            } else {
                $token = $_SESSION[$perfil][$perfil]['token'];
            }

            return [$hash, $token];
        } else {
            // vamos voltar ao inicio
            $tpl = new Template('erro_sem_sessao.html');
            $tpl->show();
            exit;
        }
    }

    public static function atribuir($perfil, $data)
    {
        if ($perfil == 'votacao') {
            $tipo = array_keys($data)[0];
            $_SESSION[$perfil][$tipo] = $data[$tipo];
        } else {
            $_SESSION[$perfil] = $data;
        }
    }

    public static function getUser()
    {
        if ($user = SELF::get('user')) {
            return $user;
        } else {
            SELF::set('next', $_SERVER['REQUEST_URI']);
            header('Location:' . getenv('WWWROOT') . '/login');
            exit;
        }
    }

    public static function isAdmin()
    {
        $user = SELF::get('user');
        if (!empty($user['admin'])) {
            return $user['admin'];
        } else {
            return false;
        }
    }

    public static function getNext()
    {
        $ret = empty($_SESSION['next']) ? getenv('WWWROOT') . '/gerente' : $_SESSION['next'];
        unset($_SESSION['next']);
        return $ret;
    }

    public static function getMsg()
    {
        $ret = empty($_SESSION['msg']) ? '' : $_SESSION['msg'];
        unset($_SESSION['msg']);
        return $ret;
    }

    // msg é array
    public static function setMsg($msg)
    {
        //$msg['class'];
        //$msg['msg'];
        return SELF::set('msg', $msg);
    }

    public static function get($chave)
    {
        return isset($_SESSION[$chave]) ? $_SESSION[$chave] : '';
    }

    public static function set($chave, $valor)
    {
        $_SESSION[$chave] = $valor;
        return true;
    }

    public static function unset($chave)
    {
        unset($_SESSION[$chave]);
        return true;
    }

    public static function getDel($chave)
    {
        $ret = empty($_SESSION[$chave]) ? '' : $_SESSION[$chave];
        unset($_SESSION[$chave]);
        return $ret;
    }
}
