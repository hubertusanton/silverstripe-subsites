<?php

/**
 * This DataObject only exists to provide a link between related pages.
 * Unfortunately, there is no way to provide a decent GUI otherwise.
 */
class RelatedPageLink extends DataObject {
	static $db = array(
	);
	
	static $has_one = array(
		'RelatedPage' => 'SiteTree',
		// Note: The *last* matching has_one relation to SiteTree is used as the link field for the
		// has_many (RelatedPages) on SiteTree.  This isn't obvious and the framework could be
		// extended in a future version to allow for explicit selection of a has_one relation to
		// bind a has_many to.
		'MasterPage' => 'SiteTree',
	);
	
	function getCMSFields() {
		$subsites = Subsite::getSubsitesForMember();
		if(!$subsites) $subsites = new DataObjectSet();
		if(Subsite::hasMainSitePermission()) {
			$subsites->push(new ArrayData(array('Title' => 'Main site', 'ID' => 0)));
		}
	
		if($subsites->Count()) {
			$subsiteSelectionField = new DropdownField(
				"CopyContentFromID_SubsiteID", 
				"Subsite", 
				$subsites->toDropdownMap('ID', 'Title'),
				($this->CopyContentFromID) ? $this->CopyContentFrom()->SubsiteID : Session::get('SubsiteID')
			);
		}
		
		// Setup the linking to the original page.
		$pageSelectionField = new SubsitesTreeDropdownField(
			"RelatedPageID", 
			_t('VirtualPage.CHOOSE', "Choose a page to link to"), 
			"SiteTree",
			"ID",
			"MenuTitle"
		);
		
		if(Controller::curr()->getRequest()) {
			$subsiteID = Controller::curr()->getRequest()->getVar('TreeDropdownField_ComplexTableField_Popup_AddForm_RelatedPageID_SubsiteID');
			$pageSelectionField->setSubsiteID($subsiteID);
		}
				
		$pageSelectionField->setFilterFunction(create_function('$item', 'return $item->ClassName != "VirtualPage";'));
		
		if($subsites->Count()) {
			$fields = new FieldSet(
				$subsiteSelectionField,
				$pageSelectionField
			);
		} else {
			$fields = new FieldSet(
				$pageSelectionField
			);
		}
		
		return $fields;
	}
	
	function RelatedPageAdminLink() {
		return '<a href="admin/show/' . $this->RelatedPage()->ID . '" class="externallink" >' . Convert::raw2xml($this->RelatedPage()->Title) . '</a>';
	}
}

?>