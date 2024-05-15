# OpenAI_PHP_JS
Achieve front-end processing and back-end processing for using OpenAI API with just one file. PHP and javascript.

[English](#Usage) / [日本語](#使い方)

## Usage

### When not using ReCAPTCHA

#### 1) Modify PHP files.

```php
const KEY = 'Specify OpenAI API KEY here';
```

#### 2) Prepare an HTML file.

- Include javascript with script tag.
- Create an instance of the OpenAI class.
    - 1st argument: OpenAI AssistantsID
    - 2nd argument: Thread ID (usually null)
    - 3rd argument: Whether to use localStorage. When used, ThreadID is saved and read.
- You can get a response from the AI ​​using the send method.
    - You can specify how long to wait for a response.
    - When the response is completed, the status property becomes "completed".
- If it takes a long time to respond, use the recieve method to poll.

```html
<script type="text/javascript" src="/php/openai.php"></script>
<script>
    const oa = new OpenAI('Specify AssistantsID here.', null, false);
    oa.send('Hello World!', 5, ()=>{ // Wait up to 5 seconds for a response.
        if (oa.status !== 'complete' && !oa.error) {
            oa.recieve(5, ()=>{
                console.log(oa.result);
            });
        } else {
            console.log(oa.result);
        }
    });
</script>
```

### When using ReCAPTCHA

#### 1) Modify PHP files.

```php
const KEY = 'Specify OpenAI API KEY here';
const RSECRET = 'Specify the ReCAPTCHA v3 secret key here';
const RMIN = 0.7; // ReCAPTCHA passing score (0.0-1.0, 0.5 recommended)
```

#### 2) Prepare an HTML file.

- Include ReCAPTCHA javascript with render parameter.
- Include javascript with script tag.
- Create an instance of the OpenAI class.
    - 1st argument: OpenAI AssistantsID
    - 2nd argument: Thread ID (usually null)
    - 3rd argument: Whether to use localStorage. When used, ThreadID is saved and read.
- You can get a response from the AI ​​using the send method.
    - You can specify how long to wait for a response.
    - When the response is completed, the status property becomes "completed".
- If it takes a long time to respond, use the recieve method to poll.

```html
<script src="https://www.google.com/recaptcha/api.js?render={Specify the ReCAPTCHA v3 site key here}"></script>
<script type="text/javascript" src="/php/openai.php"></script>
<script>
    const oa = new OpenAI('Specify AssistantsID here.', null, false);
    oa.send('Hello World!', 5, ()=>{ // Wait up to 5 seconds for a response.
        if (oa.status !== 'complete' && !oa.error) {
            oa.recieve(5, ()=>{
                console.log(oa.result);
            });
        } else {
            console.log(oa.result);
        }
    });
</script>
```

## 使い方

### ReCAPTCHAを使用しない場合

#### 1) PHP ファイルを変更します。

```php
const KEY = 'Specify OpenAI API KEY here';
```

#### 2) HTMLファイルを用意します。

- このPHPファイルを、scriptタグを使用してjavascriptとして組み込みます。
- OpenAIクラスのインスタンスを作成します。
    - 第1引数: OpenAIのAssistantsID
    - 第2引数: スレッドID（通常はnullでOK）
    - 第3引数: localStorageを使用するかどうか。使用する場合、ThreadIDが保存・読み込みされます。
- sendメソッドを使用してAIからの応答を取得できます。
    - 応答を待つ時間を秒単位で指定できます。
    - 応答が完了すると、statusプロパティは "completed" になります。
- 応答に時間がかかる場合は、receiveメソッドを使用してポーリングします。

```html
<script type="text/javascript" src="/php/openai.php"></script>
<script>
    const oa = new OpenAI('Specify AssistantsID here.', null, false);
    oa.send('Hello World!', 5, ()=>{ // Wait up to 5 seconds for a response.
        if (oa.status !== 'complete' && !oa.error) {
            oa.recieve(5, ()=>{
                console.log(oa.result);
            });
        } else {
            console.log(oa.result);
        }
    });
</script>
```

### ReCAPTCHAを使用する場合

#### 1) PHP ファイルを変更します。

```php
const KEY = 'Specify OpenAI API KEY here';
const RSECRET = 'Specify the ReCAPTCHA v3 secret key here';
const RMIN = 0.7; // ReCAPTCHA passing score (0.0-1.0, 0.5 recommended)
```

#### 2) HTMLファイルを用意します。

- ReCAPTCHAのjsライブラリをrenderパラメータ付きで呼び出します。
- このPHPファイルを、scriptタグを使用してjavascriptとして組み込みます。
- OpenAIクラスのインスタンスを作成します。
    - 第1引数: OpenAIのAssistantsID
    - 第2引数: スレッドID（通常はnullでOK）
    - 第3引数: localStorageを使用するかどうか。使用する場合、ThreadIDが保存・読み込みされます。
- sendメソッドを使用してAIからの応答を取得できます。
    - 応答を待つ時間を秒単位で指定できます。
    - 応答が完了すると、statusプロパティは "completed" になります。
- 応答に時間がかかる場合は、receiveメソッドを使用してポーリングします。

```html
<script src="https://www.google.com/recaptcha/api.js?render={Specify the ReCAPTCHA v3 site key here}"></script>
<script type="text/javascript" src="/php/openai.php"></script>
<script>
    const oa = new OpenAI('Specify AssistantsID here.', null, false);
    oa.send('Hello World!', 5, ()=>{ // Wait up to 5 seconds for a response.
        if (oa.status !== 'complete' && !oa.error) {
            oa.recieve(5, ()=>{
                console.log(oa.result);
            });
        } else {
            console.log(oa.result);
        }
    });
</script>
```


## OpenAIクラス

### コンストラクタ

- 第1引数: OpenAIのAssistantsIDを指定する。
- 第2引数: スレッドIDを指定する。通常はnullでOK。スレッドを復活させたい時にだけ指定する。
- 第3引数: localStorageを使用するかどうか。使用する場合、ThreadIDが保存・読み込みされます。

### メソッド

|名称|機能|引数|戻り値|備考|
|:--------|:--------|:--------|:--------|:--------|
|send()|AIに発言を送信し、回答が返ってくるまで指定秒数まつ|第1引数: (string)AIに対する発言|なし||
|||第2引数: (int)回答を待つ秒数||待ってもAIからの回答がない場合、statusはin_progressとなる|
|||第3引数: (function)AIから回答があった場合(status===completed)、もしくはタイムアウトした場合に呼び出される関数||関数内でstatusを見て処理を振り分けることを推奨|
|||第4引数: (function)AIがエラーになった場合に呼び出される関数||該当の送受信(=runId)は復帰できない状態|
|receive()|AIから回答が返ってくるまで指定秒数まつ|第1引数: (int)回答を待つ秒数|なし|待ってもAIからの回答がない場合、statusはin_progressとなる|
|||第2引数: (function)AIから回答があった場合(status===completed)、もしくはタイムアウトした場合に呼び出される関数||関数内でstatusを見て処理を振り分けることを推奨|
|||第3引数: (function)AIがエラーになった場合に呼び出される関数||該当の送受信(=runId)は復帰できない状態|

### メンバ変数

|推奨|名称|説明|
|:---|:----------|:----------|
|読取専用|status|文字列。in_progress または completed または faild|
|読取専用|result|statusがcompletedの場合、AI回答内容（stringもしくはobject)が格納されている。|
|読取専用|error|通常時はnull。サーバ側でエラーが発生した場合はExceptionの内容。必ずしもstatusと同期していないことに注意|
|非アクセス|assistantId|このAIを決定するID。platform.openai.comで確認した値をコンストラクタに指定すること。|
|非アクセス|threadId|会話のスレッドを決定するID。推奨されないが、threadIdを切り替えて使用することで、1つのオブジェクトで複数会話を管理可能。|
|非アクセス|runId|1回の送信について、回答を待つためのID。sendメソッドで更新され、recieveメソッドで参照される。|
|非アクセス|useLocalStorage|true: threadIdをローカルストレージに保存し、OpenAIクラスが再度newされた場合にthreadIdをローカルストレージから復活させる。　|
|非アクセス|url|REST-APIのエンドポイント。PHP側で自動的にパスが設定される。|
|非アクセス|recaptchaSiteKey|ReCAPTCHAを使用する場合、scriptタグのsrc属性からrenderパラメタに指定した値を自動的に格納する。|
