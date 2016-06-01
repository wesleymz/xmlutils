<?php 

class XmlUtils {
	
	/**
	 * @var string $_name
	 */
	protected $_name;
	
	/**
	 * @var string $_rootElement
	 */
	protected $_rootElement;
	
	/**
	 * @var string $nova_tag
	 */
	protected $nova_tag = '';
	
	/**
	 * @var string $elemento_filho
	 */
	protected $elemento_filho = '';
	
	/**
	 * Construtor da classe
	 *
	 * @param string $_name
	 */
	public function __construct($_rootElement = 'Config') {
		$this->_rootElement = $_rootElement;
	}
	
	public function setNovaTag( $nova_tag  ) {
		
		$this->nova_tag = $nova_tag;
		
	}
	
	public function getNovaTag() {
		
		return $this->nova_tag;
		
	}
	
	public function setArquivo( $arquivo ) {
		
		$this->_name = FUSION_CONFIGURATION . DIRECTORY_SEPARATOR . $arquivo;
		
	}
	
	public function getArquivo() {
		
		return $this->_name;
		
	}
	
	public function setElementoFilho( $elemento_filho ) {
	
		$this->elemento_filho = $elemento_filho;
	
	}
	
	public function getElementoFilho() {
	
		return $this->elemento_filho;
	
	}
	
	
	public function visualizaXml( $_xmlfile = null, $tipoRetorno = 'array' ) {
	
		$dadosConfig = array();
		try{
			if ($_xmlfile == null)
				$config = $this->_name;
			else
				$config = $_xmlfile;
		
			if( is_file( $config ) || ( is_array( $config ) && !empty( $config ) ) ) {
		
				if( is_file( $config ) ) {
		
					$dadosConfig = simplexml_load_file( $config );
					if($tipoRetorno == 'array')
						$dadosConfig = (array)$dadosConfig;
					
				}
		
			} else {
					
				throw new Exception( 'A configuraзгo informada nгo estб no formato vбlido!' );
					
			}
		}catch (Exception $e){
			Log::set('Erro ao visualizar XML',Log::TYPE_ERROR,null,$e);
		}
	
		return $dadosConfig;
	
	}
	
	/**
	 * Obtйm os atributos cadastrados na superClasse e subClasse.
	 * @return array
	 */
	public static function getAtributos() {
		
		$classFilho = get_called_class();
		
		$classVars[$classFilho] = array_keys(get_class_vars($classFilho));
		
		$classPai = get_parent_class($classFilho);
		
		
		while ( $classPai !== FALSE ) {
			
			$classVars[$classPai] = array_keys(get_class_vars($classPai));
			
			$classVars[$classFilho] = array_diff($classVars[$classFilho], $classVars[$classPai]);
			
			if ( isset($prevParentVars) ) {
				$prevParentVars = array_diff($prevParentVars, $classVars[$classPai]);
			}
			 
			$prevParentVars = &$classVars[$classPai];
			$classPai = get_parent_class($classPai);

		}
		
		return $classVars[$classFilho];
	}
	
	/**
	 * Popula os dados de $entrada como parametros da classe
	 *
	 * @param array $entrada - Pares de coluna-valor que serгo populados para a classe
	 * @param string $_prefixo - Prefixo das colunas
	 * @return boolean
	 */
	public function populateXml( $entrada, $_prefixo = '' ) {
		
		if( empty( $entrada ) )
			return false;
		
		$arrItens = $this->getAtributos();
		
		foreach( $arrItens as $name ) {

			$valor = null;
			$nome_campo = $_prefixo.$name;
			
			if( isset($entrada[$nome_campo]) ) {
	
				$valor = $entrada[$nome_campo];
				
			} 
			
			if( isset($valor) ) {
	
				if( $valor != 'NULL' ) {
					
					if ( is_array($valor) ){
						foreach ( $valor as $value ){
							$this->{$name} = utf8_decode( $value );
						}
					} else {
						$this->{$name} = utf8_decode( $valor );
					}
					 
				} else {
					$this->{$name} = $valor;
				}
			} // end-if
		} // end-foreach
		
		return true;
	}
	
	/**
	 * Obtйm os atributos cadastrados na superClasse e subClasse.
	 * @param array $campos - Pares de coluna-valor que serгo inseridos no arquivo Xml.
	 * @return array
	 */
	public function atualizaXml( $campos , $arq = null ) {
		
		if( !isset($campos) || empty($campos) ) {
			
			return false;
			
		} else {
		
			/* versao do encoding xml */
			$dom = new DOMDocument("1.0",_XMLENCODE_);
				
			/* retirar os espacos em branco */
			$dom->preserveWhiteSpace = false;
				
			/* gerar o codigo*/
			$dom->formatOutput = true;
				
			/* criando o nу principal (raiz) */
			$root = $dom->createElement( $this->_rootElement );
			
			/* Criando a tag filha */
			$tag_filha = $dom->createElement( str_replace( ' ', '_', $this->getElementoFilho() ) );
			
			$arrItens = $this->getAtributos();//Pegando campos do XML.
			
			foreach ($arrItens as $key => $value) {				
				$tags_xml = $value;
				$tags_xml = $dom->createElement($tags_xml, htmlspecialchars(utf8_encode($this->{$value}))); // Setanto nomes das tags xml (netos)
				$tag_filha->appendChild($tags_xml); // Adiciona as tags (informacaoes da empresa) na tag filha Empresa
			}
			
			// Adiciona a tag filha 'empresa' na raiz FusionGenConnection
			$root->appendChild($tag_filha);
			// Adiciona a raiz FusionGenConnection no arquivo Xml
			$dom->appendChild($root);
			
			$retorno = $dom->save( $this->_name );
	
		}
		return $retorno;
	}
	
	
	/**
	 * @param array $campos - Pares de coluna-valor que serгo atualizados no arquivo Xml.
	 * @return array
	 */
	public function atualizarXml( $campos, $elemento_filho=null ) {
		
		if( !isset($campos) || empty($campos) ) {
				
			return false;
				
		} else {
	
			$xml = simplexml_load_file( $this->_name );
			
			foreach($campos as $key => $value) {
				if(isset($elemento_filho))				
					$xml->$elemento_filho->$key = $value;
				else
					$xml->$key = $value;						
			}
			
			$retorno = $xml->asXML($this->_name);
				
		}
		
		return $retorno;
	}
	
	public function criarXml() {
		try {
	
			/* versao do encoding xml */
			$dom = new DOMDocument("1.0",_XMLENCODE_);
				
			/* retirar os espacos em branco */
			$dom->preserveWhiteSpace = false;
				
			/* gerar o codigo*/
			$dom->formatOutput = true;
				
			/* criando o nу principal (raiz) */
			$root = $dom->createElement( $this->_rootElement );
				
			// Adiciona a raiz FusionGenConnection no arquivo Xml
			$dom->appendChild($root);
				
			$retorno = $dom->save( $this->_name );
				
			return $retorno;
				
		}
		catch( Exception $e ) {
			return false;
			var_dump( $e->getMessage() );
		}
	
	}
	
	/**
	 * Obtйm os nуs filhos e seus dependentes.
	 * @param array $elemento_filho - Pares de coluna-valor que serгo consultados no arquivo Xml.
	 * @param array $elemento_neto -  Pares de coluna-valor que serгo consultados no arquivo Xml.
	 * @return array
	 */
	public function getNomElemento( $elemento_filho = '', $elemento_neto = '' ) { 
		
		if ( empty($elemento_filho) && empty($elemento_neto) ) {
			
			$result = simplexml_load_file( $this->_name );
			
			return $result;
				
		} else {
			
			if ( !empty($elemento_filho) && empty($elemento_neto) ) {

				$result = simplexml_load_file( $this->_name );
				$result = $result->{$elemento_filho};
			
			} else if( !empty($elemento_filho) && !empty($elemento_neto) ) {
				
				$result = simplexml_load_file( $this->_name );
				$arr_result = array();
				
				foreach ($result as $key => $value) {
					
					if ( array_key_exists($elemento_neto, $value) ) {
						
						$arr_result[$key] = (string)$value->{$elemento_neto};
						
					}
					
				}
				
				return $arr_result;
				
			}
			
		} 
		
		return $result;
		
	}
	
	
	/**
	 * Insere nуs no arquivo xml existente.
	 * @param string $tag_filho - elemento filho do arquivo xml.
	 * @param array $tag_neto - Pares de coluna-valor que serгo inseridos no elemento filho Xml.
	 * @return Xml. 
	 */
	public function inserirTag( $tag_filho = null, $tag_neto = null ) {
		
		if ( $tag_neto == null || $tag_neto == ' ' ) {
			
			return false;
			
		} else {
		
			$dom = new DOMDocument("1.0", _XMLENCODE_);
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = false;
			$root = $dom->createElement($this->_rootElement);
			$child = $dom->createElement(str_replace(' ', '_', $tag_filho));
			
			# INICIO
			// buscando o arquivo xml que serб alterado
			$arq = simplexml_load_file($this->_name);
			$tags_xml = array();
			
			foreach ( (array)$arq->$tag_filho as $key => $value ) {
				
				$tags_xml = $key;
				$tags_xml = $dom->createElement($tags_xml, $value); // Setanto nomes das tags xml (netos)
				$child->appendChild($tags_xml); // Adiciona as tags (informacaoes da empresa) na tag filha Empresa
				
			}
			# FIM
			
			# INICIO
			// inserindo um novo elemento no arquivo xml
			if ( is_array($tag_neto) ) {
				
				foreach ($tag_neto as $key => $value) {
					
					$novo_elemento = $key;
					$novo_elemento = $dom->createElement($novo_elemento, $value);
					$child->appendChild($novo_elemento);
					
				} 
				
			} else {
				
				$tag_neto = array( $tag_neto => $tag_neto);
				foreach ( $tag_neto as $key => $value) {
					
					$novo_elemento = $key;
					$novo_elemento = $dom->createElement($novo_elemento, $value);
					$child->appendChild($novo_elemento);
					
				} 
			}
			#FIM	
			
			// Adiciona a tag filha '$child' na raiz 'FusionGenConnection'
			$root->appendChild($child);
			// Adiciona a raiz 'FusionGenConnection' no arquivo Xml
			$dom->appendChild($root);
			
			$retorno = $dom->save( $this->_name );
			
			return $retorno;
			
			}
		
	}
	
	/**
	 * Exclui nуs no arquivo xml existente.
	 * @param string $tag - elemento filho do arquivo Xml.
	 * @param array $subtag - Pares de coluna-valor que serгo excluidos no elemento filho Xml.
	 * @return Xml. 
	 */
	public function excluirTag( $tag = null, $subtag = null ) {
	
		if ( $subtag == null || $subtag == ' ' ) {
			
			return false;
			
		} else {
			
			$doc = simplexml_load_file( $this->_name );
			
			if ( is_array($subtag) ) {
				
				foreach ( $subtag as $key => $value ) {
					
					// Esta funзгo recebe um nу de um documento SimpleXML e o transforma em um nу DOM. O novo objeto pode ser utilizado como um elemento DOM nativo.
					$dom = dom_import_simplexml( $doc->$tag->$value );
					// o mйtodo parentNode busca a tag superior no $doc onde encontra-se a subtag $dom e o remove.
					$dom->parentNode->removeChild($dom);
					
				}
				
			} else {
				
				$subtag = array( $subtag => $subtag ); 
				foreach ( $subtag as $key => $value ) {
					
					// Esta funзгo recebe um nу de um documento SimpleXML e o transforma em um nу DOM. O novo objeto pode ser utilizado como um elemento DOM nativo.
					$dom = dom_import_simplexml( $doc->$tag->$value );
					// o mйtodo parentNode busca a tag superior no $doc onde encontra-se a subtag $dom e o remove.
					$dom->parentNode->removeChild($dom);
					
				}
				
			}
			
			// salva o novo xml sem o elemento removido.
			$doc->saveXML( $this->_name );
			
		}
		
	}
	
		
}

?>