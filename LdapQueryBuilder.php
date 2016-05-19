<?php

namespace Symfony\Component\Ldap;

use LdapQuery\Builder;

class LdapQueryBuilder extends Builder
{   
    private $client;

    /**
     * Create a new instance of LdapQueryBuilder.
     * 
     * @param LdapClient $client
     *
     * @return void
     */
    public function __construct(LdapClient $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    /**
     * Check if method exists within accepted Builder methods for 
     * creating a query.
     * 
     * @param  string $method
     * 
     * @return boolean
     */
    public static function isValidCall($method)
    {
        $builder = new Builder;
        return in_array($method, array_merge(['where', 'orWhere'], $builder->dynamicWheres));
    }

    /**
     * Redirect method call to client.
     *
     * @param string $dn
     * @param array $fields
     * 
     * @return array
     */
    public function get($dn, array $fields = [])
    {
        return $this->client->get($dn, $fields);
    }
}
