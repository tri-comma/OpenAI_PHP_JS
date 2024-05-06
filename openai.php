<?php
// Copyright © 2024 TRI-COMMA. All rights reserved. ver 20240506
const KEY = ''; // Specify OpenAI API KEY here
const RSECRET = null; // (Optional) Specify the ReCAPTCHA v3 secret key here
const RMIN = 0.7;
const FN_SND = 'send';
const FN_RCV = 'receive';

try {
	$p = getParam();
	$p['tid'] = $p['tid'] ?: createThread()['id'];
	if ($p['fn'] == FN_SND) {
		createMessage($p['tid'], $p['msg']);
		$p['rid'] = run($p['aid'], $p['tid'])['id'];
	}
	for ($i = 0; $i < ($p['wit'] + 1); $i++) {
		$resR = getRun($p['tid'], $p['rid']);
		$resM = getMessage($p['tid'], $p['rid']);
		$res = [
			'tid' => $p['tid'],
			'rid' => $p['rid'],
			'sts' => getStatus($resR, $p['rid']),
			'msg' => getContentText($resM, $p['rid']),
		];
		if ($res['sts'] == 'completed' || $res['sts'] == 'failed') {
			break;
		}
		sleep(1);
	}
	doResponse($res);
} catch (Exception $e) {
	doResponse(['error'=>$e->getMessage(),'param'=>$p]);
}

function getParam() {
	try {
		if ($_SERVER['REQUEST_METHOD'] == 'GET') doResponseJS();
		if ($_SERVER['REQUEST_METHOD'] != 'POST') throw new Exception('This REST-API only accepts POST method.');
		$p = json_decode(file_get_contents('php://input'), true);
		if ($p == null) throw new Exception('Parameter not found. Please send JSON data with "Content-Type: application/json"');
		checkParam($p,'fn');
		if ($p['fn'] != FN_SND && $p['fn'] != FN_RCV) throw new Exception('Only "'.FN_SND.'" or "'.FN_RCV.'" can be specified for parameter "fn".');
		$p['tid'] = $p['fn'] == FN_SND ? $p['tid'] : checkParam($p,'tid');
		$p['aid'] = $p['fn'] == FN_SND ? checkParam($p,'aid') : $p['aid'];
		$p['rid'] = $p['fn'] == FN_RCV ? checkParam($p,'rid') : $p['rid'];
		$p['msg'] = $p['fn'] == FN_SND ? checkParam($p,'msg') : $p['msg'];
		$p['wit'] = $p['wit'] ?? 1;
		doRecaptcha($p);
		return $p;
	} catch (Exception $e) {
		doResponse(['error'=>$e->getMessage(),'param'=>$p]);
	}
}

function doRecaptcha($p) {
	if (RSECRET) {
		$token = checkParam($p,'token');
		$recaptch_url = 'https://www.google.com/recaptcha/api/siteverify';
		$recaptcha_params = [
			'secret' => RSECRET,
			'response' => $token,
		];
		$recaptcha = json_decode(file_get_contents($recaptch_url . '?' . http_build_query($recaptcha_params)));
		if ($recaptcha->score < RMIN) {
			throw new Exception('ReCAPTCHA Error. Score is '.$recaptcha->score);
		}
	}
}

function checkParam($param, $name) {
	if (!$param[$name]) {
		throw new Exception('Parameter "'.$name.'" is not found. This parameter is required.');
	}
	return $param[$name];
}

function createThread() {
	$url = 'https://api.openai.com/v1/threads';
	return doApi('POST', $url, []);
}

function createMessage($tid, $msg) {
	$url = 'https://api.openai.com/v1/threads/'.$tid.'/messages';
	return doApi('POST', $url, [
		'role'=>'user',
		'content'=>$msg
	]);
}

function run($aid, $tid) {
	$url = 'https://api.openai.com/v1/threads/'.$tid.'/runs';
	return doApi('POST', $url, [
		'assistant_id'=>$aid
	]);
}

function getRun($tid, $rid) {
	$url = 'https://api.openai.com/v1/threads/'.$tid.'/runs';
	return doApi('GET', $url, []);
}

function getMessage($tid, $rid) {
	$url = 'https://api.openai.com/v1/threads/'.$tid.'/messages';
	return doApi('GET', $url, []);
}

function doResponse($res) {
    header('Content-Type: application/json');
    echo json_encode($res);
    exit();
}

function doApi($method, $apiUrl, $requestData) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . KEY,
        'OpenAI-Beta: assistants=v2',
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getStatus($res, $rid) {
	$idx = array_search($rid, array_column($res['data'], 'id'));
	if ($idx === false) return null;
	return $res['data'][$idx]['status'];
}

function getContentText($res, $rid) {
	$idx = array_search($rid, array_column($res['data'], 'run_id'));
	if ($idx === false) return null;
	return $res['data'][$idx]['content'][0]['text']['value'];
}

function doResponseJS() {
    header('Content-Type: text/javascript');
    $self = ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
?>
class OpenAI {
	constructor(assistantId, threadId, useLocalStorage) {
		this.useLocalStorage = useLocalStorage;
		if (this.useLocalStorage == true) {
			this.threadId = localStorage.getItem('threadId') ?? threadId;
			this.assistantId = localStorage.getItem('assistantId') ?? assistantId;
		} else {
			this.threadId = threadId;
			this.assistantId = assistantId;
		}
		this.runId = '';
		this.onload  = (res)=>{ console.log(res); };
		this.onerror = (res)=>{ console.error(res); };
		this.url = '<?=$self ?>';
		this.result = null;
		this.status = null;
		this.error = null;
		const recaptchaJs = document.querySelector('script[src^="https://www.google.com/recaptcha/api.js"]');
		this.recaptchaSiteKey = recaptchaJs ? recaptchaJs.src.split('=')[1] : null;
	}
	send(message, waitSecond = 0, onload = this.onload, onerror = this.onerror) {
		const payload = {
			fn: 'send',
			aid: this.assistantId,
			msg: message,
			tid: this.threadId,
			wit: waitSecond,
		};
		this._doPost(this.url, payload, onload, onerror);
	}
	receive(waitSecond = 0, onload = this.onload, onerror = this.onerror) {
		const payload = {
			fn: 'receive',
			tid: this.threadId,
			rid: this.runId,
			wit: waitSecond,
		};
		this._doPost(this.url, payload, onload, onerror);
	}
	newThread() {
		this.threadId = null;
		localStorage.removeItem('threadId');
	}
	_doPost(url, data, onload, onerror) {
		const xhr = new XMLHttpRequest();
		xhr.open('POST', url);
		xhr.setRequestHeader('Content-Type', 'application/json');
		xhr.onload = () => { this._callback(xhr, onload); };
		xhr.onerror = () => { this._callback(xhr, onerror); };
		if (this.recaptchaSiteKey) {
			const sitekey = this.recaptchaSiteKey;
			const self = this;
			grecaptcha.ready(function() {
				grecaptcha.execute(sitekey, {action: 'submit'}).then(function(token) {
					data.token = token;
					xhr.send(JSON.stringify(data));
					self.result = null;
					self.status = 'justsent';
				});
			});
		} else {
			xhr.send(JSON.stringify(data));
			this.result = null;
			this.status = 'justsent';
		}
	}
	_callback(xhr, fn) {
		try {
			let res = JSON.parse(xhr.responseText);
			this.status = res.sts;
			this.error = res.error ?? null;
			if (this.status === 'completed') {
				try {
					this.result = JSON.parse(res.msg);
				} catch (e) {
					this.result = res.msg;
				}				
			}
			if (this.threadId != res.tid) {
				this.threadId = res.tid;
				if (this.useLocalStorage == true) {
					localStorage.setItem('threadId', this.threadId);
				}
			}
			this.runId = res.rid;
			fn(res);
		} catch (e) {
			console.error(e);
		}
	}
}
<?php
	exit();
}
?>
