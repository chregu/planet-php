<?php
error_reporting(error_reporting() & ~E_STRICT);
require_once dirname(__FILE__) . '/../inc/config-admin.php';
require_once 'Auth.php';
require_once 'Log.php';
require_once 'Log/observer.php';
require_once 'Validate.php';
require_once 'HTML/Template/IT.php';

class Planet
{
	protected $_db;
	
	public function __construct(PDO $db) 
	{
		$this->_db = $db;
	}

	protected function _validateRss($url) 
	{
		$xml = new DomDocument();
		if(!@$xml->load($url)) 
			return false;
		return true;
	}

	public function addFeedForm($url) 
	{
		if (empty($url)) {
			throw new Exception('Empty URL');
		}

		$options = array(
			'allowed_schemes' => array('http', 'https'),
			'strict' => ''
		);
		if (!Validate::uri($url, $options)) {
			throw new Exception('Invalid URL');
		}
				
		if (!($fp = fopen($url, "r"))) {
			throw new Exception('URL not found');
		}
		fclose($fp);

		if (!$this->_validateRss($url)) {
			throw new Exception('Invalid RSS');
		}
		
		$stmt = $this->_db->prepare('INSERT INTO feeds SET link = :url');
		$stmt->bindParam(':url', $url, PDO::PARAM_STR);
		return $stmt->execute();
	}

	public function getFeeds() 
	{
		$results = array();
		$stmt    = $this->_db->query("SELECT id, link FROM feeds ORDER BY ID");

		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$results[] = $row;
		}

		return $results;
	}

	public function deleteFeed($id)
	{
		if (empty($id)) {
			throw new Exception('Cannot delete an empty id');
		}
		
		$stmt = $this->_db->prepare('DELETE FROM feeds WHERE ID = :id');
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		return $stmt->execute();
	}
}

function listFeeds(Planet $p, HTML_Template_IT $it)
{
	$it->setCurrentBlock('list.entry');
	foreach ($p->getFeeds() as $feed) {
		$it->setVariable('id', $feed['id']);
		$it->setVariable('link', $feed['link']);
		$it->parseCurrentBlock();
	}
}

function loginFunction($username = null, $status = null, &$auth = null)
{
    echo '<form method="post" action="admin.php">';
    echo '<label for="username">PEAR User:</label> <input type="text" name="username" value="' . htmlspecialchars($username) . '"/><br/>';
    echo '<label for="password">Password:</label> <input type="password" name="password"/><br/>';
    echo '<input type="submit" value="Login"/>';
    echo '</form>';
}

/*$options = array(
//    'url'   => 'https://pear.php.net/rest-login.php',
//    'karma' => 'pear.planet.admin',
	'karma' => 'pear.dev'
	); */
$options = array();
$auth = new Auth("PEAR", $options, 'loginFunction');
$auth->start();

$debugObserver = new Log_observer(PEAR_LOG_DEBUG);
$auth->attachLogObserver($debugObvserver);

$it = new HTML_Template_IT('./templates');
$it->loadTemplatefile('index.tpl', true, true);

$pdo = new PDO(PDO_DSN, PDO_USERNAME, PDO_PASSWORD);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$planet = new Planet($pdo);

if (isset($_GET['logout']) && $auth->checkAuth()) {
    $auth->logout();
    $auth->start();
}

if ($auth->checkAuth()) {
	try {
		if (!empty($_POST['feedurl'])) {
			$planet->addFeedForm($_POST['feedurl']);
		}

		if (!empty($_GET['delete']) && empty($_GET['deleteReally'])) {
			$it->setVariable('id', (int) $_GET['delete']);
			$it->parse('feed.delete');
			$it->show();
			exit;
		}
		
		if (!empty($_GET['delete']) && !empty($_GET['deleteReally'])) {
			$planet->deleteFeed((int) $_GET['delete']);
		}
		
		$it->setVariable('error', '');
	} catch (Exception $e) {
		$it->setVariable('error', $e->getMessage());
	}

	listFeeds($planet, $it);
	$it->touchBlock('feed.add');
	$it->show();
}
