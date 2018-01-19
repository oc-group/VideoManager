<?php
require_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');
require_once('./Services/MainMenu/classes/class.ilMainMenuGUI.php');

/**
 * Class ilVideoManagerPlugin
 *
 * @author Theodor Truffer <tt@studer-ramimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilVideoManagerPlugin extends ilUserInterfaceHookPlugin {

	/**
	 * @var ilSubscriptionPlugin
	 */
	protected static $instance;


	/**
	 * @return ilVideoManagerPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return 'VideoManager';
	}


	public function isCtrlMainMenuActive() {
		global $ilPluginAdmin;

		/**
		 * @var ilPluginAdmin $ilPluginAdmin
		 */
		return in_array('CtrlMainMenu', $ilPluginAdmin->getActivePluginsForSlot('Services', 'UIComponent', 'uihk'));
	}


	/**
	 * @return bool
	 */
	public function beforeActivation() {
		//if CtrlMainMenu Plugin is active and no Video-Manager entry exists, create one
		if (self::isCtrlMainMenuActive()) {
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Dropdown/class.ctrlmmEntryDropdown.php');
			require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/Ctrl/class.ctrlmmEntryCtrl.php');

			$dropdown_entries = ctrlmmEntryDropdown::get();

			$create_dropdown = true;
			foreach ($dropdown_entries as $entry) {
				$translations = ctrlmmTranslation::_getAllTranslationsAsArray($entry->getId());
				foreach ($translations as $l => $t) {
					if ($t == 'Video-Manager') {
						$create_dropdown = false;
					}
				}
			}

			if ($create_dropdown) {
				$dropdown = new ctrlmmEntryDropdown();
				$dropdown->create();

				$admin = new ctrlmmEntryCtrl();
				$admin->setParent($dropdown->getId());
				$admin->setGuiClass('ilUIPluginRouterGUI,ilVideoManagerAdminGUI');
				$admin->create();

				$channels = new ctrlmmEntryCtrl();
				$channels->setParent($dropdown->getId());
				$channels->setGuiClass('ilUIPluginRouterGUI,ilVideoManagerUserGUI');
				$channels->create();

				foreach (array( 'en', 'de' ) as $lang) {
					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($dropdown->getId(), $lang);
					$trans->setTitle('Video-Manager');
					$trans->store();

					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($admin->getId(), $lang);
					$trans->setTitle('Administration');
					$trans->store();

					$trans = ctrlmmTranslation::_getInstanceForLanguageKey($channels->getId(), $lang);
					$trans->setTitle('Channels');
					$trans->store();
				}
			}
		}

		return true;
	}


	protected function afterActivation() {
		if (!self::isCtrlMainMenuActive()) {
			ilUtil::sendFailure($this->txt('msg_no_ctrlmm'), true);
		}
	}


	/**
	 * @param $usr_id
	 *
	 * @return ilLanguage
	 */
	public function loadLanguageForUser($usr_id) {
		$lng = ilObjUser::_lookupLanguage($usr_id);
		$ilLanguage = new ilLanguage($lng);
		$ilLanguage->loadLanguageModule("ui_uihk_video_man");

		return $ilLanguage;
	}


	//	public function txt($a_var) {
	//		require_once('./Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php');
	//		return sragPluginTranslator::getInstance($this)->active()->write()->txt($a_var);
	//		return parent::txt($a_var); // TODO: Change the autogenerated stub
	//	}
}