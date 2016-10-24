<?php 

class Infinite_ImageGallery_Block_Gallery extends Mage_Core_Block_Template
{
	protected $_gallery;

	public function _construct()
	{
		parent::_construct();

		$this->setTemplate('imagegallery/gallery.phtml');
	}

	protected function _getIdentifier()
	{
		return $this->getData('indentifier');
	}

	public function getGallery()
	{
		if(isset($this->_gallery))
			return $this->_gallery;

		$collection = Mage::getModel('imagegallery/gallery')
			->getCollection()
			->addFieldToFilter('identifier', $this->_getIdentifier())
			->addFieldToFilter('is_active', 1);

		if($collection->count())
			$this->_gallery = $collection->getFirstItem();

		return $this->_gallery;
	}

	public function getHeightOfIcon()
	{
		$imageGallery = $this->getGallery();

		if(isset($imageGallery))
			return $imageGallery->getHeightOfIcon();

		return 200;
	}

	/*public function getNoOfImagePerRow()
	{
		$imageGallery = $this->getGallery();

		if(isset($imageGallery))
			return $imageGallery->getNoOfImagesPerRow();

		return 5;
	}

	public function getImages()
	{
		$imageGallery = $this->getGallery();

		if(isset($imageGallery))
		{
			$imageGalleryId = $this->getGallery()->getId();

			return Mage::getModel('imagegallery/image')
				->getCollection()
				->addFieldToFilter('gallery_id', $imageGalleryId)
				->addFieldToFilter('is_active', 1);
		}
	} */

	public function getImages($row)
	{
		$imageGallery = $this->getGallery();

		if(isset($imageGallery))
		{
			$imageGalleryId = $this->getGallery()->getId();

			$collection = Mage::getModel('imagegallery/image')
				->getCollection()
				->addFieldToFilter('gallery_id', $imageGalleryId)
				->addFieldToFilter('image_row', $row)
				->addFieldToFilter('is_active', 1);

			$collection->setOrder('image_order','ASC');

			return $collection;
		}
	}

	public function getImageUrl($image)
	{
		return Mage::helper('imagegallery')->getImageUrl() . $image;
	}

	public function getMaxRows()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql = 'SELECT MAX(image_row) as MAX_ROW FROM ' . Mage::getSingleton('core/resource')->getTableName('imagegallery/image');
		$result = $connection->fetchAll($sql);
		if(count($result) && isset($result[0]["MAX_ROW"]))
			return $result[0]["MAX_ROW"];
		return 0;
	}
}

?>