<?php
namespace Codeception\Module;

use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Yii2 as Yii2Connector;
use Codeception\Lib\Connector\Yii2\ConnectionWatcher;
use Codeception\Lib\Connector\Yii2\Logger;
use Codeception\Lib\Connector\Yii2\TransactionForcer;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\MultiSession;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Yii;
use yii\base\Security;
use yii\db\ActiveQueryInterface;
use yii\helpers\Url;
use yii\web\Application;
use yii\web\IdentityInterface;

/**
 * This module provides integration with [Yii framework](https://www.yiiframework.com/) (2.0).
 *
 * It initializes the Yii framework in a test environment and provides actions
 * for functional testing.
 *
 * ## Application state during testing
 *
 * This section details what you can expect when using this module.
 *
 * * You will get a fresh application in `\Yii::$app` at the start of each test
 *   (available in the test and in `_before()`).
 * * Inside your test you may change application state; however these changes
 *   will be lost when doing a request if you have enabled `recreateApplication`.
 * * When executing a request via one of the request functions the `request`
 *   and `response` component are both recreated.
 * * After a request the whole application is available for inspection /
 *   interaction.
 * * You may use multiple database connections, each will use a separate
 *   transaction; to prevent accidental mistakes we will warn you if you try to
 *   connect to the same database twice but we cannot reuse the same connection.
 *
 * ## Config
 *
 * * `configFile` *required* - path to the application config file. The file
 *   should be configured for the test environment and return a configuration
 *   array.
 * * `applicationClass` - Fully qualified class name for the application. There are
 *   several ways to define the application class. Either via a `class` key in the Yii
 *   config, via specifying this codeception module configuration value or let codeception
 *   use its default value `yii\web\Application`. In a standard Yii application, this
 *   value should be either `yii\console\Application`, `yii\web\Application` or unset.
 * * `entryUrl` - initial application url (default: http://localhost/index-test.php).
 * * `entryScript` - front script title (like: index-test.php). If not set it's
 *   taken from `entryUrl`.
 * * `transaction` - (default: `true`) wrap all database connection inside a
 *   transaction and roll it back after the test. Should be disabled for
 *   acceptance testing.
 * * `cleanup` - (default: `true`) cleanup fixtures after the test
 * * `ignoreCollidingDSN` - (default: `false`) When 2 database connections use
 *   the same DSN but different settings an exception will be thrown. Set this to
 *   true to disable this behavior.
 * * `fixturesMethod` - (default: `_fixtures`) Name of the method used for
 *   creating fixtures.
 * * `responseCleanMethod` - (default: `clear`) Method for cleaning the
 *   response object. Note that this is only for multiple requests inside a
 *   single test case. Between test cases the whole application is always
 *   recreated.
 * * `requestCleanMethod` - (default: `recreate`) Method for cleaning the
 *   request object. Note that this is only for multiple requests inside a single
 *   test case. Between test cases the whole application is always recreated.
 * * `recreateComponents` - (default: `[]`) Some components change their state
 *   making them unsuitable for processing multiple requests. In production
 *   this is usually not a problem since web apps tend to die and start over
 *   after each request. This allows you to list application components that
 *   need to be recreated before each request.  As a consequence, any
 *   components specified here should not be changed inside a test since those
 *   changes will get discarded.
 * * `recreateApplication` - (default: `false`) whether to recreate the whole
 *   application before each request
 *
 * You can use this module by setting params in your `functional.suite.yml`:
 *
 * ```yaml
 * actor: FunctionalTester
 * modules:
 *     enabled:
 *         - Yii2:
 *             configFile: 'path/to/config.php'
 * ```
 *
 * ## Parts
 *
 * By default all available methods are loaded, but you can also use the `part`
 * option to select only the needed actions and to avoid conflicts. The
 * available parts are:
 *
 * * `init` - use the module only for initialization (for acceptance tests).
 * * `orm` - include only `haveRecord/grabRecord/seeRecord/dontSeeRecord` actions.
 * * `fixtures` - use fixtures inside tests with `haveFixtures/grabFixture/grabFixtures` actions.
 * * `email` - include email actions `seeEmailsIsSent/grabLastSentEmail/...`
 *
 * See [WebDriver module](https://codeception.com/docs/modules/WebDriver#Loading-Parts-from-other-Modules)
 * for general information on how to load parts of a framework module.
 *
 * ### Example (`acceptance.suite.yml`)
 *
 * ```yaml
 * actor: AcceptanceTester
 * modules:
 *     enabled:
 *         - WebDriver:
 *             url: http://127.0.0.1:8080/
 *             browser: firefox
 *         - Yii2:
 *             configFile: 'config/test.php'
 *             part: orm # allow to use AR methods
 *             transaction: false # don't wrap test in transaction
 *             cleanup: false # don't cleanup the fixtures
 *             entryScript: index-test.php
 * ```
 *
 * ## Fixtures
 *
 * This module allows to use
 * [fixtures](https://www.yiiframework.com/doc-2.0/guide-test-fixtures.html)
 * inside a test. There are two ways to do that. Fixtures can either be loaded
 * with the [haveFixtures](#haveFixtures) method inside a test:
 *
 * ```php
 * <?php
 * $I->haveFixtures(['posts' => PostsFixture::class]);
 * ```
 *
 * or, if you need to load fixtures before the test, you
 * can specify fixtures in the `_fixtures` method of a test case:
 *
 * ```php
 * <?php
 * // inside Cest file or Codeception\TestCase\Unit
 * public function _fixtures()
 * {
 *     return ['posts' => PostsFixture::class]
 * }
 * ```
 *
 * ## URL
 *
 * With this module you can also use Yii2's URL format for all codeception
 * commands that expect a URL:
 *
 * ```php
 * <?php
 * $I->amOnPage('index-test.php?r=site/index');
 * $I->amOnPage('http://localhost/index-test.php?r=site/index');
 * $I->sendAjaxPostRequest(['/user/update', 'id' => 1], ['UserForm[name]' => 'G.Hopper']);
 * ```
 *
 * ## Status
 *
 * Maintainer: **samdark**
 * Stability: **stable**
 *
 */
class Yii2 extends Framework implements ActiveRecord, MultiSession, PartedModule
{
    /**
     * Application config file must be set.
     * @var array
     */
    protected array $config = [
        'fixturesMethod' => '_fixtures',
        'cleanup'     => true,
        'ignoreCollidingDSN' => false,
        'transaction' => true,
        'entryScript' => '',
        'entryUrl'    => 'http://localhost/index-test.php',
        'responseCleanMethod' => Yii2Connector::CLEAN_CLEAR,
        'requestCleanMethod' => Yii2Connector::CLEAN_RECREATE,
        'recreateComponents' => [],
        'recreateApplication' => false,
        'closeSessionOnRecreateApplication' => true,
        'applicationClass' => null,
    ];

    protected array $requiredFields = ['configFile'];

    /**
     * @var Yii2Connector\FixturesStore[]
     */
    public array $loadedFixtures = [];

    /**
     * Helper to manage database connections
     */
    private ConnectionWatcher $connectionWatcher;

    /**
     * Helper to force database transaction
     */
    private TransactionForcer $transactionForcer;

    /**
     * @var array The contents of $_SERVER upon initialization of this object.
     * This is only used to restore it upon object destruction.
     * It MUST not be used anywhere else.
     */
    private array $server;

    private Logger $yiiLogger;

    private function getClient(): \Codeception\Lib\Connector\Yii2|null
    {
        if (isset($this->client) && !$this->client instanceof \Codeception\Lib\Connector\Yii2) {
            throw new \RuntimeException('The Yii2 module must be used with the Yii2 browser client');
        }
        return $this->client;
    }

    public function _initialize(): void
    {
        if ($this->config['transaction'] === null) {
            $this->config['transaction'] = $this->backupConfig['transaction'] = $this->config['cleanup'];
        }

        $this->defineConstants();
        $this->server = $_SERVER;
        $this->initServerGlobal();
    }


    /**
     * Module configuration changed inside a test.
     * We always re-create the application.
     */
    protected function onReconfigure(): void
    {
        parent::onReconfigure();
        $this->getClient()->resetApplication();
        $this->configureClient($this->config);
        $this->yiiLogger->getAndClearLog();
        $this->getClient()->startApp($this->yiiLogger);
    }

    /**
     * Adds the required server params.
     * Note this is done separately from the request cycle since someone might call
     * `Url::to` before doing a request, which would instantiate the request component with incorrect server params.
     */
    private function initServerGlobal(): void
    {

        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);
        $_SERVER = array_merge($_SERVER, [
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME' => $entryScript,
            'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT' => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
            'HTTPS' => parse_url($entryUrl, PHP_URL_SCHEME) === 'https'
        ]);
    }

    protected function validateConfig(): void
    {
        parent::validateConfig();

        $pathToConfig = codecept_absolute_path($this->config['configFile']);
        if (!is_file($pathToConfig)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The application config file does not exist: " . $pathToConfig
            );
        }

        if (!in_array($this->config['responseCleanMethod'], Yii2Connector::CLEAN_METHODS)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The response clean method must be one of: " . implode(", ", Yii2Connector::CLEAN_METHODS)
            );
        }

        if (!in_array($this->config['requestCleanMethod'], Yii2Connector::CLEAN_METHODS)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The response clean method must be one of: " . implode(", ", Yii2Connector::CLEAN_METHODS)
            );
        }
    }

    protected function configureClient(array $settings): void
    {
        $settings['configFile'] = codecept_absolute_path($settings['configFile']);

        foreach ($settings as $key => $value) {
            if (property_exists($this->client, $key)) {
                $this->getClient()->$key = $value;
            }
        }
        $this->getClient()->resetApplication();
    }

    /**
     * Instantiates the client based on module configuration
     */
    protected function recreateClient(): void
    {
        $entryUrl = $this->config['entryUrl'];
        $entryFile = $this->config['entryScript'] ?: basename($entryUrl);
        $entryScript = $this->config['entryScript'] ?: parse_url($entryUrl, PHP_URL_PATH);

        $this->client = new Yii2Connector([
            'SCRIPT_FILENAME' => $entryFile,
            'SCRIPT_NAME' => $entryScript,
            'SERVER_NAME' => parse_url($entryUrl, PHP_URL_HOST),
            'SERVER_PORT' => parse_url($entryUrl, PHP_URL_PORT) ?: '80',
            'HTTPS' => parse_url($entryUrl, PHP_URL_SCHEME) === 'https'
        ]);

        $this->configureClient($this->config);
    }

    public function _before(TestInterface $test): void
    {
        $this->recreateClient();
        $this->yiiLogger = new Yii2Connector\Logger();
        $this->getClient()->startApp($this->yiiLogger);

        $this->connectionWatcher = new ConnectionWatcher();
        $this->connectionWatcher->start();

        // load fixtures before db transaction
        if ($test instanceof \Codeception\Test\Cest) {
            $this->loadFixtures($test->getTestInstance());
        } elseif ($test instanceof \Codeception\Test\TestCaseWrapper) {
            $this->loadFixtures($test->getTestCase());
        } else {
            $this->loadFixtures($test);
        }


        $this->startTransactions();
    }

    /**
     * load fixtures before db transaction
     */
    private function loadFixtures(object $test): void
    {
        $this->debugSection('Fixtures', 'Loading fixtures');
        if (empty($this->loadedFixtures)
            && method_exists($test, $this->_getConfig('fixturesMethod'))
        ) {
            $connectionWatcher = new ConnectionWatcher();
            $connectionWatcher->start();
            $this->haveFixtures(call_user_func([$test, $this->_getConfig('fixturesMethod')]));
            $connectionWatcher->stop();
            $connectionWatcher->closeAll();
        }
        $this->debugSection('Fixtures', 'Done');
    }

    public function _after(TestInterface $test): void
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        $this->rollbackTransactions();

        if ($this->config['cleanup']) {
            foreach ($this->loadedFixtures as $fixture) {
                $fixture->unloadFixtures();
            }
            $this->loadedFixtures = [];
        }

        $this->getClient()?->resetApplication();

        if (isset($this->connectionWatcher)) {
            $this->connectionWatcher->stop();
            $this->connectionWatcher->closeAll();
            unset($this->connectionWatcher);
        }

        parent::_after($test);
    }

    public function _failed(TestInterface $test, $fail): void
    {
        $log = $this->yiiLogger->getAndClearLog();
        if ($log !== '') {
            $test->getMetadata()->addReport('yii-log', $log);
        }

        parent::_failed($test, $fail);
    }

    protected function startTransactions(): void
    {
        if ($this->config['transaction']) {
            $this->transactionForcer = new TransactionForcer($this->config['ignoreCollidingDSN']);
            $this->transactionForcer->start();
        }
    }

    protected function rollbackTransactions(): void
    {
        if (isset($this->transactionForcer)) {
            $this->transactionForcer->rollbackAll();
            $this->transactionForcer->stop();
            unset($this->transactionForcer);
        }
    }

    public function _parts(): array
    {
        return ['orm', 'init', 'fixtures', 'email'];
    }

    /**
     * Authenticates a user on a site without submitting a login form.
     * Use it for fast pragmatic authorization in functional tests.
     *
     * ```php
     * <?php
     * // User is found by id
     * $I->amLoggedInAs(1);
     *
     * // User object is passed as parameter
     * $admin = \app\models\User::findByUsername('admin');
     * $I->amLoggedInAs($admin);
     * ```
     * Requires the `user` component to be enabled and configured.
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function amLoggedInAs(int|string|IdentityInterface $user): void
    {
        try {
            $this->getClient()?->findAndLoginUser($user);
        } catch (ConfigurationException $e) {
            throw new ModuleException($this, $e->getMessage());
        } catch (\RuntimeException $e) {
            throw new ModuleException($this, $e->getMessage());
        }
    }

    /**
     * Creates and loads fixtures from a config.
     * The signature is the same as for the `fixtures()` method of `yii\test\FixtureTrait`
     *
     * ```php
     * <?php
     * $I->haveFixtures([
     *     'posts' => PostsFixture::class,
     *     'user' => [
     *         'class' => UserFixture::class,
     *         'dataFile' => '@tests/_data/models/user.php',
     *      ],
     * ]);
     * ```
     *
     * Note: if you need to load fixtures before a test (probably before the
     * cleanup transaction is started; `cleanup` option is `true` by default),
     * you can specify the fixtures in the `_fixtures()` method of a test case
     *
     * ```php
     * <?php
     * // inside Cest file or Codeception\TestCase\Unit
     * public function _fixtures(){
     *     return [
     *         'user' => [
     *             'class' => UserFixture::class,
     *             'dataFile' => codecept_data_dir() . 'user.php'
     *         ]
     *     ];
     * }
     * ```
     * instead of calling `haveFixtures` in Cest `_before`
     *
     * @param $fixtures
     * @part fixtures
     */
    public function haveFixtures($fixtures): void
    {
        if (empty($fixtures)) {
            return;
        }
        $fixturesStore = new Yii2Connector\FixturesStore($fixtures);
        $fixturesStore->unloadFixtures();
        $fixturesStore->loadFixtures();
        $this->loadedFixtures[] = $fixturesStore;
    }

    /**
     * Returns all loaded fixtures.
     * Array of fixture instances
     *
     * @part fixtures
     * @return array
     */
    public function grabFixtures()
    {
        if (!$this->loadedFixtures) {
            return [];
        }

        return call_user_func_array(
            'array_merge',
            array_map( // merge all fixtures from all fixture stores
                function ($fixturesStore) {
                    return $fixturesStore->getFixtures();
                },
                $this->loadedFixtures
            )
        );
    }

    /**
     * Gets a fixture by name.
     * Returns a Fixture instance. If a fixture is an instance of
     * `\yii\test\BaseActiveFixture` a second parameter can be used to return a
     * specific model:
     *
     * ```php
     * <?php
     * $I->haveFixtures(['users' => UserFixture::class]);
     *
     * $users = $I->grabFixture('users');
     *
     * // get first user by key, if a fixture is an instance of ActiveFixture
     * $user = $I->grabFixture('users', 'user1');
     * ```
     *
     * @param $name
     * @return mixed
     * @throws \Codeception\Exception\ModuleException if the fixture is not found
     * @part fixtures
     */
    public function grabFixture($name, $index = null)
    {
        $fixtures = $this->grabFixtures();
        if (!isset($fixtures[$name])) {
            throw new ModuleException($this, "Fixture $name is not loaded");
        }
        $fixture = $fixtures[$name];
        if ($index === null) {
            return $fixture;
        }
        if ($fixture instanceof \yii\test\BaseActiveFixture) {
            return $fixture->getModel($index);
        }
        throw new ModuleException($this, "Fixture $name is not an instance of ActiveFixture and can't be loaded with second parameter");
    }

    /**
     * Inserts a record into the database.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('app\models\User', array('name' => 'Davert'));
     * ?>
     * ```
     * @template T of \yii\db\ActiveRecord
     * @param class-string<T> $model
     * @param array<string, mixed> $attributes
     * @return mixed
     * @part orm
     */
    public function haveRecord(string $model, $attributes = []): mixed
    {
        /** @var T $record   **/
        $record = \Yii::createObject($model);
        $record->setAttributes($attributes, false);
        $res = $record->save(false);
        if (!$res) {
            $this->fail("Record $model was not saved: " . \yii\helpers\Json::encode($record->errors));
        }
        return $record->primaryKey;
    }

    /**
     * Checks that a record exists in the database.
     *
     * ``` php
     * $I->seeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param class-string<\yii\db\ActiveRecord> $model
     * @param array<string, mixed> $attributes
     * @part orm
     */
    public function seeRecord(string $model, array $attributes = []): void
    {
        $record = $this->findRecord($model, $attributes);
        if (!$record) {
            $this->fail("Couldn't find $model with " . json_encode($attributes));
        }
        $this->debugSection($model, json_encode($record));
    }

    /**
     * Checks that a record does not exist in the database.
     *
     * ``` php
     * $I->dontSeeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param class-string<\yii\db\ActiveRecord> $model
     * @param array<string, mixed> $attributes
     * @part orm
     */
    public function dontSeeRecord(string $model, array $attributes = []): void
    {
        $record = $this->findRecord($model, $attributes);
        $this->debugSection($model, json_encode($record));
        if ($record) {
            $this->fail("Unexpectedly managed to find $model with " . json_encode($attributes));
        }
    }

    /**
     * Retrieves a record from the database
     *
     * ``` php
     * $category = $I->grabRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param class-string<\yii\db\ActiveRecord> $model
     * @param array<string, mixed> $attributes
     * @part orm
     */
    public function grabRecord(string $model, array $attributes = []): \yii\db\ActiveRecord|null|array
    {
        return $this->findRecord($model, $attributes);
    }

    /**
     * @param class-string<\yii\db\ActiveRecord> $model Class name
     * @param array<string, mixed> $attributes
     */
    protected function findRecord(string $model, array $attributes = []): \yii\db\ActiveRecord | null | array
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Class $model does not exist");
        }
        $rc = new \ReflectionClass($model);
        if ($rc->hasMethod('find')
            /** @phpstan-ignore-next-line */
            && ($findMethod = $rc->getMethod('find'))
            && $findMethod->isStatic()
            && $findMethod->isPublic()
            && $findMethod->getNumberOfRequiredParameters() === 0
        ) {
            $activeQuery = $findMethod->invoke(null);
            if ($activeQuery instanceof ActiveQueryInterface) {
                return $activeQuery->andWhere($attributes)->one();
            }

            throw new \RuntimeException("$model::find() must return an instance of yii\db\QueryInterface");
        }
        throw new \RuntimeException("Class $model does not have a public static find() method without required parameters");
    }

    /**
     * Similar to `amOnPage` but accepts a route as first argument and params as second
     *
     * ```
     * $I->amOnRoute('site/view', ['page' => 'about']);
     * ```
     *
     * @param string $route A route
     * @param array $params Additional route parameters
     */
    public function amOnRoute(string $route, array $params = []): void
    {
        if (Yii::$app->controller === null) {
            $route = "/{$route}";
        }
        
        array_unshift($params, $route);
        $this->amOnPage(Url::to($params));
    }

    /**
     * Gets a component from the Yii container. Throws an exception if the
     * component is not available
     *
     * ```php
     * <?php
     * $mailer = $I->grabComponent('mailer');
     * ```
     *
     * @throws \Codeception\Exception\ModuleException
     * @deprecated in your tests you can use \Yii::$app directly.
     */
    public function grabComponent(string $component): null|object
    {
        try {
            return $this->getClient()->getComponent($component);
        } catch (ConfigurationException $e) {
            throw new ModuleException($this, $e->getMessage());
        }
    }

    /**
     * Checks that an email is sent.
     *
     * ```php
     * <?php
     * // check that at least 1 email was sent
     * $I->seeEmailIsSent();
     *
     * // check that only 3 emails were sent
     * $I->seeEmailIsSent(3);
     * ```
     *
     * @param int|null $num
     * @throws \Codeception\Exception\ModuleException
     * @part email
     */
    public function seeEmailIsSent(?int $num = null): void
    {
        if ($num === null) {
            $this->assertNotEmpty($this->grabSentEmails(), 'emails were sent');
            return;
        }
        $this->assertEquals($num, count($this->grabSentEmails()), 'number of sent emails is equal to ' . $num);
    }

    /**
     * Checks that no email was sent
     *
     * @part email
     */
    public function dontSeeEmailIsSent(): void
    {
        $this->seeEmailIsSent(0);
    }

    /**
     * Returns array of all sent email messages.
     * Each message implements the `yii\mail\MessageInterface` interface.
     * Useful to perform additional checks using the `Asserts` module:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $messages = $I->grabSentEmails();
     * $I->assertEquals('admin@site,com', $messages[0]->getTo());
     * ```
     *
     * @part email
     * @return array
     * @throws \Codeception\Exception\ModuleException
     */
    public function grabSentEmails(): array
    {
        try {
            return $this->getClient()->getEmails();
        } catch (ConfigurationException $e) {
            throw new ModuleException($this, $e->getMessage());
        }
    }

    /**
     * Returns the last sent email:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $message = $I->grabLastSentEmail();
     * $I->assertEquals('admin@site,com', $message->getTo());
     * ```
     * @part email
     */
    public function grabLastSentEmail(): object
    {
        $this->seeEmailIsSent();
        $messages = $this->grabSentEmails();
        return end($messages);
    }



    /**
     * Returns a list of regex patterns for recognized domain names
     *
     * @return array
     */
    public function getInternalDomains(): array
    {
        return $this->getClient()->getInternalDomains();
    }

    private function defineConstants(): void
    {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'test');
        defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);
    }

    /**
     * Sets a cookie and, if validation is enabled, signs it.
     * @param string $name The name of the cookie
     * @param string $val The value of the cookie
     * @param array $params Additional cookie params like `domain`, `path`, `expires` and `secure`.
     */
    public function setCookie($name, $val, $params = [])
    {
        parent::setCookie($name, $this->getClient()->hashCookieData($name, $val), $params);
    }

    /**
     * Creates the CSRF Cookie.
     * @param string $val The value of the CSRF token
     * @return string[] Returns an array containing the name of the CSRF param and the masked CSRF token.
     */
    public function createAndSetCsrfCookie(string $val): array
    {
        $masked = (new Security())->maskToken($val);
        $name = $this->getClient()->getCsrfParamName();
        $this->setCookie($name, $val);
        return [$name, $masked];
    }

    public function _afterSuite(): void
    {
        parent::_afterSuite();
        codecept_debug('Suite done, restoring $_SERVER to original');

        $_SERVER = $this->server;
    }

    /**
     * Initialize an empty session. Implements MultiSession.
     */
    public function _initializeSession(): void
    {
        $this->getClient()->restart();
        $this->headers = [];
        $_SESSION = [];
        $_COOKIE = [];
    }

    /**
     * Return the session content for future restoring. Implements MultiSession.
     * @return array<string, mixed> backup data
     */
    public function _backupSession(): array
    {
        if (Yii::$app instanceof Application && Yii::$app->has('session', true) && Yii::$app->session->useCustomStorage) {
            throw new ModuleException($this, "Yii2 MultiSession only supports the default session backend.");
        }
        return [
            'clientContext' => $this->getClient()->getContext(),
            'headers' => $this->headers,
            'cookie' => isset($_COOKIE) ? $_COOKIE : [],
            'session' => isset($_SESSION) ? $_SESSION : [],
        ];
    }

    /**
     * Restore a session. Implements MultiSession.
     * @param array<mixed> $session output of _backupSession()
     */
    public function _loadSession($session): void
    {
        $this->getClient()->setContext($session['clientContext']);
        $this->headers = $session['headers'];
        $_SESSION = $session['session'];
        $_COOKIE = $session['cookie'];

        // reset Yii::$app->user
        if (isset(Yii::$app)) {
            $app = Yii::$app;
            $definitions = $app->getComponents(true);
            if ($app->has('user', true)) {
                $app->set('user', $definitions['user']);
            }
        }
    }

    /**
     * Close and dump a session. Implements MultiSession.
     */
    public function _closeSession($session = null): void
    {
        if (!$session) {
            $this->_initializeSession();
        }
    }
}
