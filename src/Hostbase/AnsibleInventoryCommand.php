<?php namespace Hostbase;

use Illuminate\Console\Command;
use Shift31\HostbaseClient;
use Symfony\Component\Console\Input\InputOption;


/**
 * Class AnsibleInventoryCommand
 * @package Hostbase
 */
class AnsibleInventoryCommand extends Command
{
    const CONFIG_FILE = 'hostbase-cli.config.php';


    /**
     * @inheritdoc
     */
    protected $name = 'hostbase-ansible';

    /**
     * @var array|null
     */
    protected $groups = null;


    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        try {
            $config = $this->getConfig();
        } catch (\Exception $e) {
            print $e->getMessage() . PHP_EOL;
            exit(1);
        }

        $this->groups = isset($config['groups']) ? $config['groups'] : null;

        $this->hbClient = new HostbaseClient(
            $config['baseUrl'],
            'hosts',
            isset($config['username']) ? $config['username'] : null,
            isset($config['password']) ? $config['password'] : null
        );

        // data must be returned as an array
        $this->hbClient->decodeJsonAsArray();
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function getConfig()
    {
        $userConfigFile = getenv('HOME') . '/' . self::CONFIG_FILE;
        $systemConfigFile = '/etc/' . self::CONFIG_FILE;

        if (file_exists(self::CONFIG_FILE)) {
            $config = require(self::CONFIG_FILE);
        } elseif (file_exists($userConfigFile)) {
            $config = require($userConfigFile);
        } elseif (file_exists($systemConfigFile)) {
            $config = require($systemConfigFile);
        } else {
            throw new \Exception('No configuration file was found!');
        }

        if (!isset($config['baseUrl'])) {
            throw new \Exception("The configuration array must contain a 'baseUrl' key");
        }

        return $config;
    }


    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        return [
            ['host', 'o', InputOption::VALUE_REQUIRED, 'Show a host', null],
            ['list', 'l', InputOption::VALUE_NONE, 'List hosts by group'],
            ['limit', 'i', InputOption::VALUE_REQUIRED, 'Maximum number of hosts', 10000],
            ['list-groups', 'g', InputOption::VALUE_NONE, 'List groups']
        ];
    }


    public function fire()
    {
        if ($this->option('host')) {
            $this->showHost($this->option('host'));
        } else {
            $this->listHosts();
        }
    }


    /**
     * @param string $fqdn
     */
    protected function showHost($fqdn)
    {
        $host = $this->hbClient->show($fqdn);
        $this->output($host);
    }


    protected function listHosts()
    {
        // an empty search will yield all hosts
        $hosts = $this->hbClient->search('', $this->option('limit'), true);

        $output = ['_meta' => ['hostvars' => []]];
        $hostvars = &$output['_meta']['hostvars'];

        foreach ($hosts as $host) {
            $hostvars[$host['fqdn']] = $host;

            foreach ($this->groups as $group) {

                if (isset($host[$group])) {

                    $groupName = "{$group}-{$host[$group]}";

                    if ( ! isset($output[$groupName]['hosts'][$host['fqdn']])) {
                        $output[$groupName]['hosts'][] = $host['fqdn'];
                    }
                }

            }
        }

        // list groups, if requested
        if ($this->option('list-groups')) {
            $groups = array_keys($output);
            unset($groups[0]); // remove _meta
            natsort($groups);
            foreach($groups as $group) {
                $this->line($group);
            }
            exit(0);
        }

        $this->output($output);
    }


    /**
     * @param mixed $output
     */
    protected function output($output)
    {
        if (PHP_MAJOR_VERSION == 5 and PHP_MINOR_VERSION < 4) {
            $this->line(json_encode($output));
        } else {
            $this->line(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}