<?php

// File generated from our OpenAPI spec

namespace StripeWPFS\Service\Terminal;

class LocationService extends \StripeWPFS\Service\AbstractService
{
    /**
     * Returns a list of <code>Location</code> objects.
     *
     * @param null|array $params
     * @param null|array|\StripeWPFS\Util\RequestOptions $opts
     *
     * @throws \StripeWPFS\Exception\ApiErrorException if the request fails
     *
     * @return \StripeWPFS\Collection<\StripeWPFS\Terminal\Location>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/terminal/locations', $params, $opts);
    }

    /**
     * Creates a new <code>Location</code> object. For further details, including which
     * address fields are required in each country, see the <a
     * href="/docs/terminal/fleet/locations">Manage locations</a> guide.
     *
     * @param null|array $params
     * @param null|array|\StripeWPFS\Util\RequestOptions $opts
     *
     * @throws \StripeWPFS\Exception\ApiErrorException if the request fails
     *
     * @return \StripeWPFS\Terminal\Location
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/terminal/locations', $params, $opts);
    }

    /**
     * Deletes a <code>Location</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\StripeWPFS\Util\RequestOptions $opts
     *
     * @throws \StripeWPFS\Exception\ApiErrorException if the request fails
     *
     * @return \StripeWPFS\Terminal\Location
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }

    /**
     * Retrieves a <code>Location</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\StripeWPFS\Util\RequestOptions $opts
     *
     * @throws \StripeWPFS\Exception\ApiErrorException if the request fails
     *
     * @return \StripeWPFS\Terminal\Location
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }

    /**
     * Updates a <code>Location</code> object by setting the values of the parameters
     * passed. Any parameters not provided will be left unchanged.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\StripeWPFS\Util\RequestOptions $opts
     *
     * @throws \StripeWPFS\Exception\ApiErrorException if the request fails
     *
     * @return \StripeWPFS\Terminal\Location
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/terminal/locations/%s', $id), $params, $opts);
    }
}
