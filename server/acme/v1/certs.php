<?php
chdir(__DIR__);
require '../../../public_html/akcms/u/config/config.php';
require '../../../public_html/akcms/core/functs.php';
require '../../../public_html/akcms/core/CmsLogger.php';
require 'src/itr-acme-client.php'; //https://github.com/ITronic/itr-acme-client/
require 'simplelogger.php';

function getProjectName(){
    $path = explode(DIRECTORY_SEPARATOR,getcwd());
    $projectName = $path[3];
    return $projectName;
}

$projectName = getProjectName();

try {
    // Create the itrAcmeClient object
    $iac = new itrAcmeClient();

    // Activate debug mode, we automatically use staging endpoint in testing mode
    $iac->testing = !in_array('--prod',$_SERVER['argv']);

    // The root directory of the certificate store
    $iac->certDir = "/data/certs/$projectName";

    if ($iac->testing) $iac->certDir .= '/test';

    // The root directory of the account store
    $iac->certAccountDir = $iac->certDir."/account";
    // This token will be attached to the $certAccountDir
    $iac->certAccountToken = '';

    $iac->certKeyTypes = ['RSA'];


    if (file_exists($iac->certDir . '/cert.crt')) {
        $oldKey = file_get_contents($iac->certDir . '/cert.crt');
        $parsed = openssl_x509_parse($oldKey);
        $expireDate = DateTime::createFromFormat('ymdHise', $parsed['validTo']);
        $ramain = $expireDate->getTimestamp() - time();
        echo @$parsed['name'].PHP_EOL;
        echo '  Сертификат до '.$expireDate->format('Y-m-d H:i:s.vP').PHP_EOL;
        echo '  '.@$parsed['extensions']['subjectAltName'].PHP_EOL;
        echo '  Осталось: '.intervalToWordsExact($ramain).' ('.intdiv($ramain,86400).' дней)'.PHP_EOL;
        if ($ramain>86400*mt_rand(30,45)) { echo 'Еще рано.'.PHP_EOL; die; }
    }

    // The certificate contact information
    $iac->certAccountContact = [
        "mailto:aliday.pr+$projectName@gmail.com",
        //'tel:+43123123123'
    ];

//    $iac->certDistinguishedName = [
//        /** @var string The certificate ISO 3166 country code */
//        'countryName'            => 'AT',
//        'stateOrProvinceName'    => 'Vienna',
//        'localityName'           => 'Vienna',
//        'organizationName'       => 'Example Company',
//        'organizationalUnitName' => 'Webserver',
//        'street'                 => 'Example street'
//    ];

    $iac->webRootDir          = '/data/certs/www';
    $iac->appendDomain        = false;
    $iac->appendWellKnownPath = true;

    // A \Psr\Log\LoggerInterface or null The logger to use
    // At the end of this file we have as simplePsrLogger implemntation
    $iac->logger = new simplePsrLogger;

    if (!file_exists($iac->certDir)) mkdir($iac->certDir,0750,true);

    // Initialise the object
    $iac->init();

    // Create an account if it doesn't exists
    $iac->createAccount();

    // The Domains we want to sign
    $domains = $cfg['server_prod'];

    // Sign the Domains and get the certificates
    $pem = $iac->signDomains($domains);

    file_put_contents($iac->certDir . '/cert.crt', $pem['RSA']['cert']);
    file_put_contents($iac->certDir . '/key.pem', $pem['RSA']['key']);
    file_put_contents($iac->certDir . '/chain.pem', $pem['RSA']['chain']);

    file_put_contents($iac->certDir . '/fullchain.pem', $pem['RSA']['cert']);
    file_put_contents($iac->certDir . '/fullchain.pem', $pem['RSA']['chain'],FILE_APPEND);


    // Output the certificate informatione
    //print_r($pem);

} catch (\Throwable $e) {
    print_r($e->getMessage());
    print_r($e->getTraceAsString());
}

