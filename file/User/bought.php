<!DOCTYPE html>
<html lang="ja">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./design/design.css">
    <title>Bought</title>
    </head>
    <body>
<?php
    ini_set('display_errors', 0);
    session_start();
    require_once("../dbconnect.php");
    $pdo = dbconnect();
    // 非ログイン時に遷移しない
    if($_SESSION["login_user"] === NULL) {
      redirect("./home.php", "不正なアクセスです。");
    }
    if(empty($_POST["email"])==false){
        $email = $_POST["email"];
    }
    if(!empty($_SESSION["login_user"])) {
        $user = $_SESSION["login_user"];
        $usermail = current(array_slice($user, 3, 1));
    }   
            $sql = "SELECT*FROM shoppingcart where email=:email";
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':email',$usermail,PDO::PARAM_STR);
            $stmt->execute();
            $results1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
            /*foreach($results1 as $row){
                require 'src/Exception.php';
                require 'src/PHPMailer.php';
                require 'src/SMTP.php';
                require 'setting.php';
                    $to = $_POST["mail"];←ユーザーのログイン情報からメアドを入れる
                    // PHPMailerのインスタンス生成
                    $mail = new PHPMailer\PHPMailer\PHPMailer();
                    $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
                    $mail->SMTPAuth = true;
                    $mail->Host = MAIL_HOST; // メインのSMTPサーバーを指定する
                    $mail->Username = MAIL_USERNAME; // SMTPユーザー名
                    $mail->Password = MAIL_PASSWORD; // SMTPパスワード
                    $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、 「SSL」も受け入れます
                    $mail->Port = SMTP_PORT; // 接続するTCPポート
                    // メール内容設定
                    $mail->CharSet = "UTF-8";
                    $mail->Encoding = "base64";
                    $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
                    $mail->addAddress($to, '受信者'); //受信者（送信先）を追加する
                    //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
                    //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
                    //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
                    $mail->Subject = MAIL_SUBJECT; // メールタイトル
                    $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
                    $body = '購入ありがとうございました。
                            【購入情報】
                            <div class="productinfo">
                    <table border="1">
                        <tbody>
                            <tr>
                                <td style="background-color: #1B4353;" width="150" align="center">
                                    <font color="#ffffff"><strong>No.</strong></td>
                                <td style="background-color: #1B4353;" width="150">
                                    <font color="#ffffff"><strong>商品名</strong></td>
                                <td style="background-color: #1B4353;" width="150">
                                    <font color="#ffffff"><strong>数量</strong></td>
                                <td style="background-color: #1B4353;" width="150">
                                    <font color="#ffffff"><strong>料金</strong></td>      
                            </tr>
                            <tr>
                                <td style="background-color:rgb(248,248,248);" width="150" align="center">
                                    <?php echo $row['buysirialno'];?></td>
                                <td style="background-color:rgb(248,248,248);" width="150">
                                    <?php echo $row['name'];?></td>
                                <td style="background-color:rgb(248,248,248);" width="150">
                                    <?php echo $row['buynumber'];?></td>
                                <td style="background-color:rgb(248,248,248);" width="150">
                                    <?php echo $row['price'];?>円</td>
                            </tr>
                        </tbody>
                    </table> 
                </div>';
                    $mail->Body  = $body; // メール本文
                    // メール送信の実行
                    if(!$mail->send()) {
                        echo 'メッセージは送られませんでした！';
                        echo 'Mailer Error: ' . $mail->ErrorInfo;
                    } else {
                        echo '送信完了！';
                    }
            }*/  
            foreach($results1 as $row){
                //cart1の変数定義
                $buysirialno = $row['buysirialno'];
                $buynumber = $row['buynumber'];
                $sql = 'SELECT*FROM fooddata where sirialno=:sirialno';
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':sirialno',$buysirialno,PDO::PARAM_STR);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($results as $row){
                    //eeeeの変数定義
                    $sirialno = $row['sirialno'];
                    $number = $row['number']; 
                    //商品数-購入量=$newnumber（購入後、残りの商品数）
                    $newnumber = $number - $buynumber;
                    if($newnumber > 0){
                        //eeee内の商品数を編集
                        $sql = 'UPDATE fooddata SET number = :number WHERE sirialno = :sirialno';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':number', $newnumber, PDO::PARAM_STR);
                        $stmt->bindParam(':sirialno', $sirialno, PDO::PARAM_STR);
                        $stmt->execute();
                        $sql = 'delete from shoppingcart';
	                    $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                        
                        //$sql = "SELECT*FROM eeee ORDER BY sirialno ASC";
                        //$stmt = $pdo->query($sql);
                        //$results = $stmt->fetchAll();                        
                    }elseif($newnumber == 0){
                        //eeeeの変数定義
                        $sql = 'delete from fooddata where sirialno=:sirialno';
                        $stmt = $pdo->prepare($sql);
	                    $stmt->bindParam(':sirialno', $sirialno, PDO::PARAM_STR);
                        $stmt->execute();
                        $sql = 'delete from shoppingcart';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                    }else{
                        echo "在庫不足です。選び直して下さい。";
                        $sql = 'delete from shoppingcart';
	                    $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                    }
                }    
            }
?>                        
            <h1>購入が完了しました！</h1>
            <form method="post" action="mypage.php" align="center">
                <input type="hidden" name="email" value="<?php echo "$email"; ?>"> 
                <a href="mypage.php" style="text-decoration: none;">  
                    <input type="submit" value="商品一覧ページに戻る" class="btn-flat-simple">
                </a>
            </form>  
    </body>
</html>