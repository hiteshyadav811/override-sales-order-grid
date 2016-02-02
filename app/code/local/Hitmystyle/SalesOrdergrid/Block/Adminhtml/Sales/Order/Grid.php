<?php
class Hitmystyle_SalesOrdergrid_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	public function __construct()
	{
		Mage_Adminhtml_Block_Widget_Grid::__construct();
		$this->setId('sales_order_grid');
		$this->setUseAjax(true);
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}
	/**
	* Retrieve collection class
	*
	* @return string
	*/
	protected function _getCollectionClass()
	{
		return 'sales/order_grid_collection';
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel($this->_getCollectionClass());

		//Table Decalration
		$salesFlatOrder = (string)Mage::getConfig()->getTablePrefix() . 'sales_flat_order';
		$salesFlatOrderPayment = (string)Mage::getConfig()->getTablePrefix() . 'sales_flat_order_payment';

		$collection->getSelect()->join(array('sales_flat_order' => $salesFlatOrder),
		"(sales_flat_order.entity_id=main_table.entity_id)",array('base_subtotal','sales_flat_order.increment_id as sfo_id')
		);

		$collection->getSelect()->join(array('sales_flat_order_payment' => $salesFlatOrderPayment),
		"(sales_flat_order_payment.parent_id=main_table.entity_id)",array('method')
		);
		//echo $collection->printlogquery('true');

		$this->setCollection($collection);
		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();   /* this is must to get your customization collection */
	}

	protected function _prepareColumns()
	{
		$this->addColumn('increment_id', array(
		'header'=> Mage::helper('sales')->__('Order #'),
		'width' => '80px',
		'type'  => 'text',
		'index' => 'increment_id',
		'filter_index'=>'main_table.increment_id',
		));

		if (!Mage::app()->isSingleStoreMode()) {
		$this->addColumn('store_id', array(
		'header'    => Mage::helper('sales')->__('Purchased From!!!! (Store)'),
		'index'     => 'store_id',
		'type'      => 'store',
		'store_view'=> true,
		'display_deleted' => true,
		));
		}
		$this->addColumn('created_at', array(
		'header' => Mage::helper('sales')->__('Purchased On'),
		'index' => 'created_at',
		'type' => 'datetime',
		'width' => '100px',
		));

		$this->addColumn('billing_name', array(
		'header' => Mage::helper('sales')->__('Bill to Name'),
		'index' => 'billing_name',
		));

		$this->addColumn('shipping_name', array(
		'header' => Mage::helper('sales')->__('Ship to Name'),
		'index' => 'shipping_name',
		));

		$this->addColumn('method', array(
		'header' => Mage::helper('sales')->__('Payment Method'),
		'index' => 'method',
		'filter_index'=>'sales_flat_order_payment.method',
		));

		$this->addColumn('base_subtotal', array(
		'header' => Mage::helper('sales')->__('Subtotal'),
		'index' => 'base_subtotal',
		'type'  => 'currency',
		'currency' => 'base_currency_code',
		'filter_index'=>'sales_flat_order.base_subtotal',
		));

		$this->addColumn('base_grand_total', array(
		'header' => Mage::helper('sales')->__('G.T. (Base)'),
		'index' => 'base_grand_total',
		'type'  => 'currency',
		'currency' => 'base_currency_code',
		));

		$this->addColumn('grand_total', array(
		'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
		'index' => 'grand_total',
		'type'  => 'currency',
		'currency' => 'order_currency_code',
		));

		$this->addColumn('status', array(
		'header' => Mage::helper('sales')->__('Status'),
		'index' => 'status',
		'type'  => 'options',
		'width' => '70px',
		'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
		));

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
		$this->addColumn('action',
		array(
		'header'    => Mage::helper('sales')->__('Action'),
		'width'     => '50px',
		'type'      => 'action',
		'getter'     => 'getId',
		'actions'   => array(
		array(
		'caption' => Mage::helper('sales')->__('View'),
		'url'     => array('base'=>'*/sales_order/view'),
		'field'   => 'order_id'
		)
		),
		'filter'    => false,
		'sortable'  => false,
		'index'     => 'stores',
		'is_system' => true,
		));
		}
		$this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

		return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();   /* must */
	}
}