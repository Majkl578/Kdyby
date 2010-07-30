<?php


namespace Kdyby\Application;



/**
 * Description of SeoRouter
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class SeoRouter extends \Nette\Application\Route
{
	
	public function match(IHttpRequest $httpRequest)
	{
		$request = parent::match($httpRequest);

		if( $request === NULL ){ return NULL; }

		$params = $request->getParams();

		$data = dibi::fetch(
				'SELECT [modul],[presenter] FROM %n', $this->table,
				'WHERE %n = %s', 'id', $params['id']
			);

		if( empty($data) ){ return NULL; }

		return new PresenterRequest(
		  $data->modul.':'.$data->presenter,
		  $httpRequest->getMethod(),
		  $params,
		  $httpRequest->getPost(),
		  $httpRequest->getFiles(),
		  array('secured' => $httpRequest->isSecured())
		);

	}


  public function constructUrl(PresenterRequest $httpRequest, IHttpRequest $context)
  {

      $actualParams = $httpRequest->getParams();

      $data = dibi::fetch(
                    'SELECT [uri],[lang] FROM %n', $this->table,
                    'WHERE %n = %s', 'id', $actualParams['id']
            );
      if (empty($data)) return NULL;

      $uri = $context->getUri()->basePath.$data['lang'].'/'.$actualParams['id'].'-'.$data['uri'];
      //unset($actualParams['lang'], $actualParams['id'],$actualParams['uri']);
      //$query = http_build_query($actualParams, '', '&');
      //if ($query !== '') $uri .= '?' . $query;

      return $uri;

  }
}
