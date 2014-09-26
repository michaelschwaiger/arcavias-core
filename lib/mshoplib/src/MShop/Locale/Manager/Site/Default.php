<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package MShop
 * @subpackage Locale
 */


/**
 * Default implementation for managing sites.
 *
 * @package MShop
 * @subpackage Locale
 */
class MShop_Locale_Manager_Site_Default
	extends MShop_Common_Manager_Abstract
	implements MShop_Locale_Manager_Site_Interface
{
	private $_cache = array();

	private $_searchConfig = array(
		'locale.site.id' => array(
			'code' => 'locale.site.id',
			'internalcode' => 'mlocsi."id"',
			'internaldeps' => array( 'LEFT JOIN "mshop_locale_site" AS mlocsi ON (mloc."siteid" = mlocsi."id")' ),
			'label' => 'Locale site ID',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'locale.site.siteid' => array(
			'code' => 'locale.site.siteid',
			'internalcode' => 'mlocsi."id"',
			'label' => 'Locale site ID',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'locale.site.code' => array(
			'code' => 'locale.site.code',
			'internalcode' => 'mlocsi."code"',
			'label' => 'Locale site code',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'locale.site.label' => array(
			'code' => 'locale.site.label',
			'internalcode' => 'mlocsi."label"',
			'label' => 'Locale site label',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'locale.site.config' => array(
			'code' => 'locale.site.config',
			'internalcode' => 'mlocsi."config"',
			'label' => 'Locale site config',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'locale.site.status' => array(
			'code' => 'locale.site.status',
			'internalcode' => 'mlocsi."status"',
			'label' => 'Locale site status',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
		),
		'locale.site.ctime'=> array(
			'code'=>'locale.site.ctime',
			'internalcode'=>'mlocsi."ctime"',
			'label'=>'Locale site create date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR
		),
		'locale.site.mtime'=> array(
			'code'=>'locale.site.mtime',
			'internalcode'=>'mlocsi."mtime"',
			'label'=>'Locale site modification date/time',
			'type'=> 'datetime',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR
		),
		'locale.site.editor'=> array(
			'code'=>'locale.site.editor',
			'internalcode'=>'mlocsi."editor"',
			'label'=>'Locale site editor',
			'type'=> 'string',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_STR
		),
		'level' => array(
			'code'=>'locale.site.level',
			'internalcode'=>'mlocsi."level"',
			'label'=>'Locale site tree level',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'left' => array(
			'code'=>'locale.site.left',
			'internalcode'=>'mlocsi."nleft"',
			'label'=>'Locale site left value',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'right' => array(
			'code'=>'locale.site.right',
			'internalcode'=>'mlocsi."nright"',
			'label'=>'Locale site right value',
			'type'=> 'integer',
			'internaltype'=> MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
	);


	/**
	 * Initializes the object.
	 *
	 * @param MShop_Context_Item_Interface $context Context object
	 */
	public function __construct( MShop_Context_Item_Interface $context )
	{
		parent::__construct( $context );
		$this->_setResourceName( 'db-locale' );
	}


	/**
	 * Creates a new site object.
	 *
	 * @return MShop_Locale_Item_Site_Interface
	 * @throws MShop_Locale_Exception
	 */
	public function createItem()
	{
		return $this->_createItem();
	}


	/**
	 * Adds a new site to the storage or updates an existing one.
	 *
	 * @param MShop_Common_Item_Interface $item New site item for saving to the storage
	 * @param boolean $fetch True if the new ID should be returned in the item
	 * @throws MShop_Locale_Exception
	 */
	public function saveItem( MShop_Common_Item_Interface $item, $fetch = true )
	{
		$iface = 'MShop_Locale_Item_Site_Interface';
		if ( !( $item instanceof $iface ) ) {
			throw new MShop_Locale_Exception(sprintf('Object is not of required type "%1$s"', $iface));
		}

		if( $item->getId() === null ) {
			throw new MShop_Locale_Exception( sprintf( 'Newly created site can not be saved using method "saveItem()". Try using method "insertItem()" instead.' ) );
		}

		if( !$item->isModified() ) { return	; }


		$context = $this->_getContext();

		$dbm = $context->getDatabaseManager();
		$dbname = $this->_getResourceName();
		$conn = $dbm->acquire( $dbname );

		try
		{
			$id = $item->getId();

			$path = 'mshop/locale/manager/site/default/item/update';
			$stmt = $this->_getCachedStatement( $conn, $path );

			$stmt->bind(1, $item->getCode(), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(2, $item->getLabel(), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(3, json_encode($item->getConfig()), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(4, $item->getStatus(), MW_DB_Statement_Abstract::PARAM_INT);
			$stmt->bind(5, $context->getEditor() );
			$stmt->bind(6, date( 'Y-m-d H:i:s', time() ) ); // mtime
			$stmt->bind(7, $id, MW_DB_Statement_Abstract::PARAM_INT);

			$stmt->execute()->finish();
			$item->setId( $id ); // set Modified false

			$dbm->release( $conn, $dbname );
		}
		catch ( Exception $e )
		{
			$dbm->release( $conn, $dbname );
			throw $e;
		}
	}


	/**
	 * Removes multiple items specified by ids in the array.
	 *
	 * @param array $ids List of IDs
	 */
	public function deleteItems( array $ids )
	{
		$context = $this->_getContext();
		$config = $context->getConfig();

		$path = 'mshop/locale/manager/site/default/item/delete';
		$this->_deleteItems($ids, $config->get( $path, $path ), false );

		$path = 'mshop/locale/manager/site/cleanup/shop/domains';
		$default = array(
			'attribute', 'catalog', 'catalog/index', 'coupon', 'customer',
			'media', 'order', 'plugin', 'price', 'product', 'product/tag',
			'service', 'supplier', 'text'
		);

		foreach( $config->get( $path, $default ) as $domain ) {
			MShop_Factory::createManager( $context, $domain )->cleanup( $ids );
		}

		$path = 'mshop/locale/manager/site/cleanup/admin/domains';
		$default = array( 'job', 'log', 'cache' );

		foreach( $config->get( $path, $default ) as $domain ) {
			MAdmin_Factory::createManager( $context, $domain )->cleanup( $ids );
		}
	}


	/**
	 * Returns the site item specified by its ID.
	 *
	 * @param string $id Unique ID of the site data in the storage
	 * @param array $ref List of domains to fetch list items and referenced items for
	 * @return MShop_Locale_Item_Site_Interface Returns the site item of the given id
	 * @throws MShop_Exception If the item couldn't be found
	 */
	public function getItem( $id, array $ref = array() )
	{
		return $this->_getItem( 'locale.site.id', $id, $ref );
	}


	/**
	 * Returns the attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array List of attribute items implementing MW_Common_Criteria_Attribute_Interface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		/** classes/locale/manager/site/submanagers
		 * List of manager names that can be instantiated by the locale site manager
		 *
		 * Managers provide a generic interface to the underlying storage.
		 * Each manager has or can have sub-managers caring about particular
		 * aspects. Each of these sub-managers can be instantiated by its
		 * parent manager using the getSubManager() method.
		 *
		 * The search keys from sub-managers can be normally used in the
		 * manager as well. It allows you to search for items of the manager
		 * using the search keys of the sub-managers to further limit the
		 * retrieved list of items.
		 *
		 * @param array List of sub-manager names
		 * @since 2014.03
		 * @category Developer
		 */
		$path = 'classes/locale/manager/site/submanagers';

		return $this->_getSearchAttributes( $this->_searchConfig, $path, array(), $withsub );
	}


	/**
	 * Returns a new sub manager of the given type and name.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return MShop_Locale_Manager_Interface manager
	 */
	public function getSubManager( $manager, $name = null )
	{
		/** classes/locale/manager/site/name
		 * Class name of the used locale site manager implementation
		 *
		 * Each default locale site manager can be replaced by an alternative imlementation.
		 * To use this implementation, you have to set the last part of the class
		 * name as configuration value so the manager factory knows which class it
		 * has to instantiate.
		 *
		 * For example, if the name of the default class is
		 *
		 *  MShop_Locale_Manager_Site_Default
		 *
		 * and you want to replace it with your own version named
		 *
		 *  MShop_Locale_Manager_Site_Mysite
		 *
		 * then you have to set the this configuration option:
		 *
		 *  classes/locale/manager/site/name = Mysite
		 *
		 * The value is the last part of your own class name and it's case sensitive,
		 * so take care that the configuration value is exactly named like the last
		 * part of the class name.
		 *
		 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
		 * characters are possible! You should always start the last part of the class
		 * name with an upper case character and continue only with lower case characters
		 * or numbers. Avoid chamel case names like "MySite"!
		 *
		 * @param string Last part of the class name
		 * @since 2014.03
		 * @category Developer
		 */

		/** mshop/locale/manager/site/decorators/excludes
		 * Excludes decorators added by the "common" option from the locale site manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "mshop/common/manager/decorators/default" before they are wrapped
		 * around the locale site manager.
		 *
		 *  mshop/locale/manager/site/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("MShop_Common_Manager_Decorator_*") added via
		 * "mshop/common/manager/decorators/default" for the locale site manager.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/locale/manager/site/decorators/global
		 * @see mshop/locale/manager/site/decorators/local
		 */

		/** mshop/locale/manager/site/decorators/global
		 * Adds a list of globally available decorators only to the locale site manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("MShop_Common_Manager_Decorator_*") around the locale site manager.
		 *
		 *  mshop/locale/manager/site/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "MShop_Common_Manager_Decorator_Decorator1" only to the locale controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/locale/manager/site/decorators/excludes
		 * @see mshop/locale/manager/site/decorators/local
		 */

		/** mshop/locale/manager/site/decorators/local
		 * Adds a list of local decorators only to the locale site manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("MShop_Common_Manager_Decorator_*") around the locale site manager.
		 *
		 *  mshop/locale/manager/site/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "MShop_Common_Manager_Decorator_Decorator2" only to the locale
		 * controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/locale/manager/site/decorators/excludes
		 * @see mshop/locale/manager/site/decorators/global
		 */

		return $this->_getSubManager( 'locale', 'site/' . $manager, $name );
	}


	/**
	 * Searches for site items matching the given criteria.
	 *
	 * @param MW_Common_Criteria_Interface $search Search object
	 * @param integer &$total Number of items that are available in total
	 * @return array List of site items implementing MShop_Locale_Item_Site_Interface
	 *
	 * @throws MW_DB_Exception On failures with the db object
	 * @throws MShop_Common_Exception On failures with the MW_Common_Criteria_ object
	 * @throws MShop_Locale_Exception On failures with the site item object
	 */
	public function searchItems( MW_Common_Criteria_Interface $search, array $ref = array(), &$total = null )
	{
		$items = array();
		$context = $this->_getContext();

		$dbm = $context->getDatabaseManager();
		$dbname = $this->_getResourceName();
		$conn = $dbm->acquire( $dbname );

		try
		{
			$attributes = $this->getSearchAttributes();
			$types = $this->_getSearchTypes($attributes);
			$translations = $this->_getSearchTranslations($attributes);

			$find = array( ':cond', ':order', ':start', ':size' );
			$replace = array(
				$search->getConditionString($types, $translations),
				$search->getSortationString($types, $translations),
				$search->getSliceStart(),
				$search->getSliceSize(),
			);

			$path = 'mshop/locale/manager/site/default/item/search';
			$sql = $context->getConfig()->get($path, $path);
			$results = $this->_getSearchResults($conn, str_replace($find, $replace, $sql));

			try
			{
				while ( ($row = $results->fetch()) !== false )
				{
					$config = $row['config'];
					if ( ( $row['config'] = json_decode( $row['config'], true ) ) === null )
					{
						$msg = sprintf( 'Invalid JSON as result of search for ID "%2$s" in "%1$s": %3$s', 'mshop_locale.config', $row['id'], $config );
						$this->_getContext()->getLogger()->log( $msg, MW_Logger_Abstract::WARN );
					}

					$items[ $row['id'] ] = $this->_createItem( $row );
				}
			}
			catch ( Exception $e )
			{
				$results->finish();
				throw $e;
			}

			if ( $total !== null )
			{
				$path = 'mshop/locale/manager/site/default/item/count';
				$sql = $this->_getContext()->getConfig()->get($path, $path);
				$results = $this->_getSearchResults($conn, str_replace($find, $replace, $sql));

				$row = $results->fetch();
				$results->finish();

				if ( $row === false ) {
					throw new MShop_Locale_Exception('No total results value found');
				}

				$total = $row['count'];
			}

			$dbm->release( $conn, $dbname );
		}
		catch ( Exception $e )
		{
			$dbm->release( $conn, $dbname );
			throw $e;
		}

		return $items;
	}


	/**
	 * Creates a search object and sets base criteria.
	 *
	 * @param boolean $default
	 * @return MW_Common_Criteria_Interface
	 */
	public function createSearch( $default = false )
	{
		if ( $default === true ) {
			$search = parent::_createSearch('locale.site');
		} else {
			$search = parent::createSearch();
		}

		$expr = array(
			$search->compare( '==', 'locale.site.level', 0 ),
			$search->getConditions(),
		);

		$search->setConditions( $search->combine( '&&', $expr ) );

		return $search;
	}


	/**
	 * Returns a list of item IDs, that are in the path of given item ID.
	 *
	 * @param integer $id ID of item to get the path for
	 * @param array $ref List of domains to fetch list items and referenced items for
	 * @return MShop_Locale_Item_Site_Interface[] Associative list of items implementing MShop_Locale_Item_Site_Interface with IDs as keys
	 */
	public function getPath( $id, array $ref = array() )
	{
		$item = $this->getTree( $id, $ref, MW_Tree_Manager_Abstract::LEVEL_ONE );
		return array( $item->getId() => $item );
	}


	/**
	 * Returns a node and its descendants depending on the given resource.
	 *
	 * @param integer|null $id Retrieve nodes starting from the given ID
	 * @param array List of domains (e.g. text, media, etc.) whose referenced items should be attached to the objects
	 * @param integer $level One of the level constants from MW_Tree_Manager_Abstract
	 * @return MShop_Locale_Item_Site_Interface Site item
	 */
	public function getTree( $id = null, array $ref = array(), $level = MW_Tree_Manager_Abstract::LEVEL_TREE )
	{
		if( $id !== null )
		{
			if( count( $ref ) > 0 ) {
				return $this->getItem( $id, $ref );
			}

			if( !isset( $this->_cache[$id] ) ) {
				$this->_cache[$id] = $this->getItem( $id, $ref );
			}

			return $this->_cache[$id];
		}

		$criteria = $this->createSearch();
		$criteria->setConditions( $criteria->compare( '==', 'locale.site.code', 'default' ) );
		$criteria->setSlice( 0, 1 );

		$items = $this->searchItems( $criteria, $ref );

		if( ( $item = reset( $items ) ) === false ) {
			throw new MShop_Locale_Exception( sprintf( 'Tree root with code "%1$s" in "%2$s" not found', 'default', 'locale.site.code' ) );
		}

		$this->_cache[ $item->getId() ] = $item;

		return $item;
	}


	/**
	 * Adds a new item object.
	 *
	 * @param MShop_Locale_Item_Site_Interface $item Item which should be inserted
	 * @param integer $parentId ID of the parent item where the item should be inserted into
	 * @param integer $refId ID of the item where the item should be inserted before (null to append)
	 */
	public function insertItem( MShop_Locale_Item_Site_Interface $item, $parentId = null, $refId = null )
	{
		$context = $this->_getContext();

		$dbm = $context->getDatabaseManager();
		$dbname = $this->_getResourceName();
		$conn = $dbm->acquire( $dbname );

		try
		{
			$curdate = date( 'Y-m-d H:i:s' );

			$path = 'mshop/locale/manager/site/default/item/insert';
			$stmt = $this->_getCachedStatement( $conn, $path );

			$stmt->bind(1, $item->getCode(), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(2, $item->getLabel(), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(3, json_encode($item->getConfig()), MW_DB_Statement_Abstract::PARAM_STR);
			$stmt->bind(4, $item->getStatus(), MW_DB_Statement_Abstract::PARAM_INT);
			$stmt->bind(5, 0, MW_DB_Statement_Abstract::PARAM_INT);
			$stmt->bind(6, $context->getEditor() );
			$stmt->bind(7, $curdate ); // mtime
			$stmt->bind(8, $curdate ); // ctime

			$stmt->execute()->finish();

			$path = 'mshop/locale/manager/default/item/newid';
			$item->setId( $this->_newId( $conn, $context->getConfig()->get( $path, $path ) ) );

			$dbm->release( $conn, $dbname );
		}
		catch ( Exception $e )
		{
			$dbm->release( $conn, $dbname );
			throw $e;
		}
	}


	/**
	 * Moves an existing item to the new parent in the storage.
	 *
	 * @param mixed $id ID of the item that should be moved
	 * @param mixed $oldParentId ID of the old parent item which currently contains the item that should be removed
	 * @param mixed $newParentId ID of the new parent item where the item should be moved to
	 * @param mixed $refId ID of the item where the item should be inserted before (null to append)
	 */
	public function moveItem( $id, $oldParentId, $newParentId, $refId = null )
	{
		throw new MShop_Locale_Exception( sprintf( 'Method "%1$s" for locale site manager not available', 'moveItem()' ) );
	}


	/**
	 * Returns the search results for the given SQL statement.
	 *
	 * @param MW_DB_Connection_Interface $conn Database connection
	 * @param $sql SQL statement
	 * @return MW_DB_Result_Interface Search result object
	 */
	protected function _getSearchResults( MW_DB_Connection_Interface $conn, $sql )
	{
		$statement = $conn->create($sql);
		$this->_getContext()->getLogger()->log(__METHOD__ . ': SQL statement: ' . $statement, MW_Logger_Abstract::DEBUG);

		$results = $statement->execute();

		return $results;
	}


	/**
	 * Create new item object initialized with given parameters.
	 *
	 * @return MShop_Locale_Item_Site_Interface
	 */
	protected function _createItem( array $data = array( ) )
	{
		return new MShop_Locale_Item_Site_Default($data);
	}


	/**
	 * Returns the raw search config array.
	 *
	 * @return array List of search config arrays
	 */
	protected function _getSearchConfig()
	{
		return $this->_searchConfig;
	}
}
