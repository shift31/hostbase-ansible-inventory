# Hostbase Ansible Inventory

A [dynamic inventory](http://docs.ansible.com/intro_dynamic_inventory.html) script for [Ansible](http://www.ansible.com).

## Requirements

* [A Hostbase server](https://github.com/shift31/hostbase)
* PHP >= 5.4

## Installation

1. Download the PHAR:  https://github.com/shift31/hostbase-ansible-inventory/raw/master/hostbase-ansible.phar
2. Move it to your preferred location for Ansible inventory scripts
3. Make it executable:

    ```bash
	chmod +x hostbase-ansible.phar
	```

## Configuration

Create `hostbase-cli.config.php` in your current directory, your home directory, or in /etc.

'groups' should be an array of Hostbase host object key names to be used for grouping hosts together.

```php
<?php

 return [
     'baseUrl' => 'http://your.hostbase.server',
     'groups' => ['dataCenter', 'environment']
 ];
```

## Usage
`hostbase-ansible [-o|--host="..."] [-l|--list] [-i|--limit="..."] [-g|--list-groups]`


### Help
`hostbase-ansible.phar -h`

### Options

     --host (-o)           Show a host
     --list (-l)           List hosts by group
     --limit (-i)          Maximum number of hosts (default: 10000)
     --list-groups (-g)    List groups
     
     
## Reference

### Ansible Docs
* [Patterns](http://docs.ansible.com/intro_patterns.html)
* [Developing Dynamic Inventory Sources](http://docs.ansible.com/developing_inventory.html)

