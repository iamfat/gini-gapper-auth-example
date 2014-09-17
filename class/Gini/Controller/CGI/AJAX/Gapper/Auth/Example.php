<?php

namespace Gini\Controller\CGI\AJAX\Gapper\Auth;

class Example extends \Gini\Controller\CGI
{
    private static $_RPC = [];
    private function _getRPC($type='gapper')
    {
        if (!self::$_RPC[$type]) {
            try {
                $api = \Gini\Config::get($type . '.url');
                $client_id = \Gini\Config::get($type . '.client_id');
                $client_secret = \Gini\Config::get($type . '.client_secret');
                $rpc = \Gini\IoC::construct('\Gini\RPC', $api, $type);
                $bool = $rpc->authorize($client_id, $client_secret);
                if (!$bool) {
                    throw new \Exception('Your APP was not registered in gapper server!');
                }
            } catch (\Gini\RPC\Exception $e) {
            }

            self::$_RPC[$type] = $rpc;
        }

        return self::$_RPC[$type];
    }

    private function _showJSON($data)
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

    private function _showHTML($view, array $data=[])
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\HTML', V($view, $data));
    }

    public function actionLogin()
    {
        if (\Gini\Gapper\Client::getLoginStep()===\Gini\Gapper\Client::STEP_DONE) {
            return $this->_showJSON(true);
        }

        $form = $this->form('post');
        $username = $form['username'];
        $password = $form['password'];
        $bool = $this->_getRPC()->user->verify($username, $password);

        if ($bool) {
            \Gini\Gapper\Client::loginByUserName($username);
            $result = \Gini\Gapper\Client::loginByUserName($username);
            if ($result) {
                return $this->_showJSON(true);
            }
        }

        return $this->_showJSON(T('Login failed! Please try again.'));
    }

    public function actionGetForm()
    {
        $infos = (array)\Gini\Config::get('gapper.auth');
        $info = (object)$infos['example'];

        return $this->_showHTML('gapper/auth/example/login', [
            'info'=> $info
        ]);
    }
}

