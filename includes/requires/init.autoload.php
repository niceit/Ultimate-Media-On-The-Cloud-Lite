<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }

return [
    'requires/system.requirements.inc',
    'systems/classes/Validation.class',
    'systems/classes/PhpRockets_UltimateMedia_Root',
    'systems/classes/PhpRockets_UltimateMedia_Config',
    'systems/classes/PhpRockets_UltimateMedia',
    'systems/classes/PhpRockets_Models',
    'addons/PhpRockets_UCM_Addons',
    'systems/models/PhpRockets_Model_Accounts',
    'systems/classes/PhpRockets_UltimateMedia_Hooks',
    'systems/classes/PhpRockets_UltimateMedia_Attachment',
    'systems/classes/PhpRockets_UltimateMedia_Settings',
    'functions/functions',
];