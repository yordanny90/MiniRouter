<?php
namespace MiniCtrl;
if(!class_exists('MiniCtrl\\Request')){
	/**
	 * Provee las funciones básicas para la lectura de datos del Request
	 * Class Request
	 * @package MiniCtrl
	 */
	class Request{
		const CONTENTYPE_NONE='';
		const CONTENTYPE_PLAIN='text/plain';
		const CONTENTYPE_HTML='text/html';
		const CONTENTYPE_JSON='application/json';
		const CONTENTYPE_XML='application/xml';
		const CONTENTYPE_FORM_URLENCODED='application/x-www-form-urlencoded';
		const CONTENTYPE_FORM_DATA='multipart/form-data';

		static final function isAjax(){
			return (self::getHeader('X-Requested-With')=='XMLHttpRequest');
		}

		static final function getMethod(){
			return (isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'');
		}

		/**
		 * Se recomienda recibir datos por este medio solo si el método del Request es POST
		 * @return bool
		 */
		static final function isMultipart_FormData(){
			$ct=self::getContentType(true);
			return ($ct[0]==self::CONTENTYPE_FORM_DATA);
		}

		static final function getContentType($asArray=false){
			$ct=(isset($_SERVER['CONTENT_TYPE'])?$_SERVER['CONTENT_TYPE']:'');
			if($asArray){
				$ct=explode(';', $ct);
				$ct=array_map('trim', $ct);
			}
			return $ct;
		}

		static final function getInput(){
			return file_get_contents('php://input');
		}

		/**
		 * @param array $content_types
		 * @param bool $throw_required
		 * @return bool|false|mixed|\SimpleXMLElement|string|null
		 * @throws MiniCtrlError
		 */
		static final function getInputDecoded($content_types=array(), $throw_required=false){
			$ct=self::getContentType(true);
			if(count($content_types)>0 && !in_array($ct[0], $content_types)){
				if($throw_required){
					if(empty($ct[0])){
						throw (new MiniCtrlError('Content-Type no recibido'))->typeBadRequest();
					}
					else{
						throw (new MiniCtrlError('Content-Type no permitido: '.$ct[0]))->typeBadRequest();
					}
				}
				else{
					return false;
				}
			}
			$input_data=null;
			if($ct[0]==self::CONTENTYPE_NONE){
				return null;
			}
			elseif($ct[0]==self::CONTENTYPE_PLAIN || $ct[0]==self::CONTENTYPE_HTML){
				$input_data=self::getInput();
			}
			elseif($ct[0]==self::CONTENTYPE_JSON){
				$input_data=self::getInput_JSON();
			}
			elseif($ct[0]==self::CONTENTYPE_XML){
				$input_data=self::getInput_XML();
			}
			elseif($ct[0]==self::CONTENTYPE_FORM_DATA){
				$input_data=null;
			}
			elseif($ct[0]==self::CONTENTYPE_FORM_URLENCODED){
				$input_data=self::getInput_UrlEncoded();
			}
			if($input_data===null || $input_data===false){
				if($throw_required){
					throw (new MiniCtrlError('Content-Type fallo durante lectura'))->typeBadRequest();
				}
			}
			return $input_data;
		}

		static final function getInput_JSON($assoc=false){
			return json_decode(self::getInput(), $assoc);
		}

		static final function getInput_XML(){
			return simplexml_load_string(self::getInput());
		}

		static final function getInput_UrlEncoded(){
			$result=null;
			parse_str(self::getInput(), $result);
			return $result;
		}

		static final function getAllHeaders(){
			$headers=array();
			foreach($_SERVER AS $k=>$v){
				if(substr($k,0,5)=='HTTP_'){
					$k=mb_convert_case(str_replace('_','-',substr($k, 5)), MB_CASE_TITLE);
					$headers[$k]=$v;
				}
			}
			return $headers;
		}

		/**
		 * @param $name
		 * @return mixed|null
		 */
		static final function getHeader($name){
			$index='HTTP_'.strtoupper(str_replace('-', '_', $name));
			if(isset($_SERVER[$index])){
				return $_SERVER[$index];
			}
			return null;
		}

		static final function getIpCliente(){
		    return $_SERVER['REMOTE_ADDR'];
        }
	}
}