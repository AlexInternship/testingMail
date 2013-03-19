<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
            $this->sendMail();
                
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				//use 'contact' view from views/mail
				$mail = new YiiMailer('contact', array('message' => $model->body, 'name' => $model->name, 'description' => 'Contact form'));
				//render HTML mail, layout is set from config file or with $mail->setLayout('layoutName')
				$mail->render();
				//set properties as usually with PHPMailer
				$mail->From = $model->email;
				$mail->FromName = $model->name;
				$mail->Subject = $model->subject;
				$mail->AddAddress(Yii::app()->params['adminEmail']);
                            	if ($mail->Send()) {
					$mail->ClearAddresses();
					Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				} else {
					Yii::app()->user->setFlash('error','Error while sending email: '.$mail->ErrorInfo);
				}
				
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
        
        public function sendMail(){
            Yii::import('application.extensions.phpmailer.JPhpMailer');
            $mail = new JPhpMailer;
            
            $mail->SMTPSecure = "ssl";  
            $mail->Host='smtp.gmail.com';  
            $mail->Port='465';  
            $mail->Username = 'testingphpmails@gmail.com';
            $mail->Password = 'ehfportal';
            $mail->SMTPKeepAlive = true;  
            $mail->Mailer = "smtp"; 
            $mail->IsSMTP(); // telling the class to use SMTP  
            $mail->SMTPAuth   = true;                  // enable SMTP authentication  
            $mail->CharSet = 'utf-8';  
            $mail->SMTPDebug  = 0;   
            $mail->SetFrom('testingphpmails@gmail.com', 'Tester');
            $mail->Subject = 'PHPMailer Test Subject via smtp, basic with authentication';
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            $mail->MsgHTML('<h1>JUST A TEST!</h1>');
            $mail->AddAddress('childofstars@gmail.com', 'John Doe');
            $mail->Send();
        }
  
        function xmlToArray($xml,$ns=null){
          $a = array();
          for($xml->rewind(); $xml->valid(); $xml->next()) {
            $key = $xml->key();
            if(!isset($a[$key])) { $a[$key] = array(); $i=0; }
            else $i = count($a[$key]);
            $simple = true;
            foreach($xml->current()->attributes() as $k=>$v) {
                $a[$key][$i][$k]=(string)$v;
                $simple = false;
            }
            if($ns) foreach($ns as $nid=>$name) {
              foreach($xml->current()->attributes($name) as $k=>$v) {
                 $a[$key][$i][$nid.':'.$k]=(string)$v;
                 $simple = false;
              }
            } 
            if($xml->hasChildren()) {
                if($simple) $a[$key][$i] = xmlToArray($xml->current(), $ns);
                else $a[$key][$i]['content'] = xmlToArray($xml->current(), $ns);
            } else {
                if($simple) $a[$key][$i] = strval($xml->current());
                else $a[$key][$i]['content'] = strval($xml->current());
            }
            $i++;
          }
          return $a;
        }
}