<?php
session_start();

//バリデーション
//入力が正しく設定されているか確認し、不正な場合はユーザーに再入力/再選択を促す機能

//何か不備があった時に内容を格納する配列
$errors = [];

// check.phpから「戻る」できた場合
if (isset($_GET['action']) && $_GET['action'] == 'rewrite') {
    $_POST['input_name'] = $_SESSION['51_LearnSNS']['name'];
    $_POST['input_email'] = $_SESSION['51_LearnSNS']['email'];
    $_POST['input_password'] = $_SESSION['51_LearnSNS']['password'];
    //書き直すために戻ってきたという不備として扱う
    $errors['rewrite'] = true;
}

$name = '';
$email = '';
//POST送信時
//empty(配列)
//指定された配列が空の場合true, 空ではない場合false
//!は論理は反映させる
if (!empty($_POST)) {
    //$_POST
    //スーパーグローバル変数
    //連想配列で値を保持している
    //keyはinputタグに設定されたnameに属性

    //フォーム送信された値の取得
    $name = $_POST['input_name'];
    $email = $_POST['input_email'];
    $password = $_POST['input_password'];

    //空かどうかチェック
    if ($name == '') {
        //echo '空です';
        $errors['name'] = 'blank';
    }

    if ($email == '') {
        $errors['email'] = 'blank';
    }

    //passwrdの文字数の取得
    //strlen(文字列)
    //文字数を算出する
    $count = strlen($password);
    if ($password == '') {
        $errors['password'] = 'blank';
    } elseif ($count < 4 || 16 < $count) {
        //空じゃない時
        //4文字未満、または、17文字以上
        $errors['password'] = 'length';
    }

    //画像のチェック
    //input type="file"で送られるもの
    //ファイスに関しては$_FILESというスーパーグローバル変数を使用
    //$FILESを利用するための決まりごと
    //  1.formタグにenctype="multipart/form-data"が指定されている
    //  2.formタグにmethed="POST"が指定されている
    //$_FILESの利用方法
    //  $_FILES[キー]['name']ファイル名
    // $_FILES[キー]['tmp_name']データそのもの

    //画像の取得
    $file_name = '';
    if (!isset($_GET['action'])) {
        $file_name = $_FILES['input_img_name']['name'];
    }
    //画像名か空かどうかチェック　＝ 画像が未選択かチェック
    if ($file_name != '') {
        //画像が選択されていたとき

        //拡張子のチェック
        //substr(文字列、開始1)
        //指定した文字列のn文字目から文字を取得する
        //$str =substr('abcdefghi' 0)
        //echgi $str => defghi
        //$str = substr('abcdefghi', -1);
        //echo $str; => i

        $file_type = substr($file_name, -3);

        //大文字は全て小文字化
        //strtolower(文字列)
        //指定された文字列を小文字に変換する

        $file_type = strtolower($file_type);

        if ($file_type != 'jpg' && $file_type != 'png' && $file_type != 'gif') {
            $errors['img_name'] = 'type';
        }
    } else {
        //g画像が選択の時
        $errors['img_name'] = 'blank';
    }
}

   //バリエーション全て通過時
   if (empty($errors)) {
       //画像のアップロード
       //画像自体はフォルダに保存
       //画像のパスはDBに保存

       //一意のファイル名を生成
       //20190300514052529hoge.png
       //現在の日時分秒を取得
       //date(フォーマート)
       //指定されたフォーマートで現在の日時を取得する
       $date_str = date('YmdHis');
       //タイムスタンプとファイル名を結合して一意のファイル名を生成する
       $submit_file_name = $date_str.$file_name;

       //アップロード処理
       // move_uploaded_file(ファイル、アップロード先)
       move_uploaded_file($_FILES['input_img_name']['tmp_name'], '../user_profile_img/'.$submit_file_name);
       // ../は１個上のフォルダという意味

       //セッションに保存されたデータを保存
       //セッションとは
       //各サーバーに用意された一時的にデータを保存する機能
       //同一サーバー内であれば横断的に利用することができる
       //PHPでは$_SESSIONを利用する
       //使用ルール
       //   ファイルの先頭にsession_start()を記述する

       //セッションに値を格納
       //連想配列形式
       $_SESSION['51_LearnSNS']['name'] = $name;
       $_SESSION['51_LearnSNS']['email'] = $email;
       $_SESSION['51_LearnSNS']['password'] = $password;
       $_SESSION['51_LearnSNS']['img_name'] = $submit_file_name;

       // 画面遷移
       header('Location: check.php');
       exit();
   }

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Learn SNS</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body style="margin-top: 60px">
    <div class="container">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2 thumbnail">
                <h2 class="text-center content_header">アカウント作成</h2>
                <!--
                method 送信方法
                action 送信先
                何処にどんな方法で値を送るのか見る
                -->
                <!--
                signup.phpdで値に不備がないか確認したのち,OKだったらcheck.phpに連絡する
                = 値の送信先はsignup.php
                -->
                <form method="POST" action="signup.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">ユーザー名</label>
                        <input type="text" name="input_name" class="form-control" id="name" placeholder="山田 太郎"
                            value="">
                            <!--ユーザー名に関するバリエーションメッセージ -->
                            <!-- 
                                $errorsにnameというキーが存在する且つその場合がblankである
                             -->
                             <!-- 
                                 isset(連想配列[key])メモリ上に
                              -->
                        <?php if (isset($errors['name']) && $errors['name'] == 'blank'): ?>
                            <p class="text-danger">
                            ユーザを入力して下さい </p>
                        <?php endif; ?>

                    </div>
                    <div class="form-group">
                        <label for="email">メールアドレス</label>
                        <input type="email" name="input_email" class="form-control" id="email" placeholder="example@gmail.com"
                            value="">
                            <?php if (isset($errors['email']) && $errors['email'] == 'blank'): ?>
                            <p class="text-danger">
                            Eメールを入力して下さい </p>
                        <?php endif; ?>

                    </div>
                    <div class="form-group">
                        <label for="password">パスワード</label>
                        <input type="password" name="input_password" class="form-control" id="password" placeholder="4 ~ 16文字のパスワード">
                    </div>
                    <?php if (isset($errors['password']) && $errors['password'] == 'blank'): ?>
                            <p class="text-danger">
                            パスワードを入力してください </p>
                        <?php endif; ?>

                    <?php if (isset($errors['password']) && $errors['password'] == 'length'): ?>
                            <p class="text-danger">
                            パスワードは　４〜１６文字で入力してください </p>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                        <p class="test-danger">
                        パスワードを再度入力してください</p>
                        <?php endif; ?>

                    <div class="form-group">
                        <label for="img_name">プロフィール画像</label>
                        <input type="file" name="input_img_name" id="img_name" accept="image/*">
                        <?php if (isset($errors['img_name']) && $errors['img_name'] == 'blank'): ?>
                            <p class="text-danger">
                            画像を選択してください </p>
                        <?php endif; ?>

                        <?php if (isset($errors['img_name']) && $errors['img_name'] == 'type'): ?>
                            <p class="text-danger">
                            拡張子がjpg,png、gifの画像を選択してください </p>
                        <?php endif; ?>
                        
                    </div>
                    <input type="submit" class="btn btn-default" value="確認">
                    <span style="float: right; padding-top: 6px;">ログインは
                        <a href="../signin.php">こちら</a>
                    </span>
                </form>
            </div>
        </div>
    </div>
</body>
<script src="../assets/js/jquery-3.1.1.js"></script>
<script src="../assets/js/jquery-migrate-1.4.1.js"></script>
<script src="../assets/js/bootstrap.js"></script>
</html>