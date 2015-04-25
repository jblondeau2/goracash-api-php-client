<?php
/**
 * Copyright 2015 Goracash
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Goracash;

use Goracash\Http\Request as Request;
use Goracash\Http\Response as Response;
use Goracash\Service\Exception;

abstract class Service
{

    public $version;
    public $serviceName;
    public $servicePath;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Return the associated Goracash Client
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $url
     * @param array $data
     * @param string $method
     * @param array $headers
     * @return Response
     */
    protected function execute($url, array $data = array(), $method = 'POST', array $headers = array())
    {
        $data['_version'] = $this->version;
        $data['_serviceName'] = $this->serviceName;
        $data['_servicePath'] = $this->servicePath;

        $data['_applicationName'] = $this->client->getApplicationName();
        $data['_client_id'] = $this->client->getClientId();
        $data['_access_token'] = $this->client->getAccessToken();

        $url = Utils::concatPath(
            $this->client->getBasePath(), '/',
            $this->servicePath, '/',
            $url
        );
        $request = new Request($url, $method, $headers, $data);

        return $this->client->getIo()->executeRequest($request);
    }

    /**
     * @param Response $response
     * @return array
     * @throws Exception
     */
    protected function normalize(Response $response)
    {
        $data = (array)json_decode($response->body, true);
        if (!$data || $data['status'] != 'ok') {
            $this->client->getLogger()->error('Service error:', $data);
            throw new Exception($data['message']);
        }
        return $data;
    }
}