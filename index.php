<?php
require 'vendor/autoload.php';
    
$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
));

$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'slim_session',
    'secret' => 'PAPA_BEAR_HAD_A_CHAIR',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));

$view = $app->view();
$view->parseOptions = array(
    'debug' => true
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app) {
    $app->render('home.twig', array(
        'page' => 'home'
    ));
})->name('home');

$app->get('/about', function() use($app) {
    $app->render('about.twig', array(
        'page' => 'about'
    ));
})->name('about');

$app->get('/bubbleromp', function() use($app) {
    $app->render('bubbleromp.twig', array(
        'page' => 'bubbleromp'
    ));
})->name('bubbleromp');

$app->get('/bubbleromp/play', function() use($app) {
    $app->render('ibr/index.html');
})->name('bubbleromp');

$app->get('/contact', function() use($app) {
    $app->render('contact.twig', array(
        'page' => 'contact'
    ));
})->name('contact');

$app->post('/contact', function() use($app) {
    $name = $app->request->post('name');
    $email = $app->request->post('email');
    $msg = $app->request->post('msg');
    
    if (!empty($name) && !empty($email) && !empty($msg)) {
        $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
        $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        $cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
    } else {
        //message the user there was a problem
        $app->flash('error', 'Please fill out all of the fields in the contact form.');
        $app->redirect('/contact');
    }
    
    $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
    $mailer = Swift_Mailer::newInstance($transport);
    
    $message = \Swift_Message::newInstance();
    $message->setSubject('Email From Our Website');
    $message->setFrom(array(
       $cleanEmail => $cleanName
    ));
    $message->setTo(array('kvwulp@gmail.com'));
    $message->setBody($cleanMsg);
    
    $result = $mailer->send($message);
    
    if ($result) {
        $app->flash('success', 'Your email has been sent. Thank you!');
        $app->redirect('/');
    } else {
        //send message failed to sendmail
        //log error
        $app->flash('error', 'I\'m sorry, there was a problem sending your email.');
        $app->redirect('/contact');
    }
});

$app->run();