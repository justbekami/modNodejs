<?php

class modNodejs {

	public $modx;

	function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;

		$this->config = array_merge(array(
			'token' =>  $this->modx->getOption('modnodejs_token', $config, ''),
			'host' => $this->modx->getOption('modnodejs_host', $config, 'http://' . $_SERVER['HTTP_HOST'] . ':9090'),
			'assetsUrl' => $this->modx->getOption('assets_url') . 'components/modnodejs/',
		), $config);

		$this->modx->addPackage('modnodejs', $this->modx->getOption('core_path') .  'components/modnodejs/model/');
	}


	public function initialize($ctx = 'web', $scriptProperties = array()) {
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		if (empty($this->initialized[$ctx])) {
            $config_js = array(
                'ctx' => $ctx,
                'host' => $this->config['host'],
            );
            switch($ctx) {
				case 'mgr':
					$this->modx->controller->addHtml('<script type="text/javascript">modNodejsConfig=' . json_encode($config_js) . ';</script>');
					$this->modx->controller->addLastJavascript($this->config['assetsUrl'] . 'socket.io.js');
					$this->modx->controller->addLastJavascript($this->config['assetsUrl'] . $this->modx->getOption('modnodejs_manager_js'));
					$this->modx->controller->addCss($this->config['assetsUrl'] . $this->modx->getOption('modnodejs_manager_css'));
					break;
				default:
					$this->modx->regClientStartupScript('<script type="text/javascript">modNodejsConfig=' . json_encode($config_js) . ';</script>', true);
					$this->modx->regClientScript($this->config['assetsUrl'] . 'socket.io.js');
					$this->modx->regClientScript($this->config['assetsUrl'] . $this->modx->getOption('modnodejs_frontend_js'));
					$this->modx->regClientCss($this->config['assetsUrl'] . $this->modx->getOption('modnodejs_frontend_css'));
					break;
			}
            $this->initialized[$ctx] = true;
        }
	}

	// кастомизированный invokeEvent
    public function invokeEvent($eventName, array $params = array(), $glue = '<br/>') {
        $response = $this->modx->invokeEvent($eventName, $params);
        if (is_array($response) && count($response) > 1) {
            foreach ($response as $k => $v) {
                if (empty($v)) {
                    unset($response[$k]);
                }
            }
        }

        $response = count($response) == 1 ? array_shift($response) : $response;
        return array(
            'success' => true,
            'data' => $response,
        );
    }

	// отправка запроса в nodejs
	public function emit($action,  $data = null, $target = null) {
		if(!is_array($data)) return;

	 	$target = parse_url($target ?: $this->config['host']);
	 	$token = $this->config['token'];
 		$host = $target['host'];
 		$port = $target['port'];
 		$scheme = $target['scheme'];
 		$path = $target['path'] ?: '/socket.io/';
 		$query = $target['query'] ? "?{$target['query']}" : "?EIO=2&transport=websocket";

 		$fd = fsockopen($host, $port, $errno, $errstr);
        if (!$fd) return false;
        $key = $this->generateKey();
        $out = "GET {$path}{$query} HTTP/1.1\r\n";
        $out.= "Host: {$scheme}://{$host}:{$port}\r\n";
        $out.= "Upgrade: WebSocket\r\n";
        $out.= "Connection: Upgrade\r\n";
        $out.= "Sec-WebSocket-Token: {$token}\r\n";
        $out.= "Sec-WebSocket-Key: {$key}\r\n";
        $out.= "Sec-WebSocket-Version: 13\r\n";
        $out.= "Origin: *\r\n\r\n";

		fwrite($fd, $out);
        $result= fread($fd,10000);
		preg_match('#Sec-WebSocket-Accept:\s(.*)$#mU', $result, $matches);
        $keyAccept = trim($matches[1]);
        $expectedResonse = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $handshaked = ($keyAccept === $expectedResonse) ? true : false;
        if ($handshaked){
            fwrite($fd, $this->hybi10Encode('42["' . $action . '", "' . addslashes($this->modx->toJSON($data)) . '"]'));
            fread($fd,1000000);
            return true;
        } else {
			return false;
		}
    }

	private function generateKey($length = 16) {
        $c = 0;
        $tmp = '';
        while ($c++ * 16 < $length) { $tmp .= md5(mt_rand(), true); }
        return base64_encode(substr($tmp, 0, $length));
    }

    private function hybi10Encode($payload, $type = 'text', $masked = true) {
        $frameHead = array();
        $payloadLength = strlen($payload);
        switch ($type) {
            case 'text':
                $frameHead[0] = 129;
                break;
            case 'close':
                $frameHead[0] = 136;
                break;
            case 'ping':
                $frameHead[0] = 137;
                break;
            case 'pong':
                $frameHead[0] = 138;
                break;
        }
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }

}