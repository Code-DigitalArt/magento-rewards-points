<?php
/**
 * Company: Unleaded Group.
 * Developer: Jason Roy
 * Email: Jason@UnleadedGroup.com
 * Module: Unleaded_Jrewards
 */

 
$installer = $this;
$installer->startSetup();

$eav = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId = $eav->getEntityTypeId('customer');
$attributeSetId = $eav->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $eav->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$eav->removeAttribute('customer', 'jrewards_member');
$eav->removeAttribute('customer', 'jrewards_text_messaging');
$eav->removeAttribute('customer', 'jrewards_cell_number');
$eav->removeAttribute('customer', 'jrewards_secondary_email');
$eav->removeAttribute('customer', 'jrewards_verify_code');
$eav->removeAttribute('customer', 'jrewards_reward_id');
$eav->removeAttribute('customer', 'jrewards_reward_amount');
$eav->removeAttribute('customer', 'jrewards_home_number');
$eav->removeAttribute('customer', 'jrewards_preference');
$eav->removeAttribute('customer', 'jrewards_quote_before');
$eav->removeAttribute('customer', 'jrewards_quote_after');

$eav
    ->addAttribute(
        'customer',
        'jrewards_member',
        [
            'type' => 'int',
            'input' => 'checkbox',
            'label' => 'JackRabbit Terms and Conditions',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1,
            'default' => 0,
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_text_messaging',
        [
            'type' => 'int',
            'input' => 'checkbox',
            'label' => 'JackRabbit Text Messaging',
            'global' => 1,
            'visible' => 1,
            'requried' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1,
            'default' => 0
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_secondary_email',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Secondary Email',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_verify_code',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Verification Code',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_cell_number',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Cell Phone Number',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_reward_id',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Reward Id',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_reward_amount',
        [
            'type' => 'int',
            'input' => 'text',
            'label' => 'Reward Amount',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_home_number',
        [
            'type' => 'varchar',
            'input' => 'text',
            'label' => 'Home Phone Number',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_preference',
        [
            'type' => 'varchar',
            'input' => 'checkbox',
            'label' => 'Gender Preference',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_quote_before',
        [
            'type' => 'varchar',
            'input' => 'checkbox',
            'label' => 'Gender Preference',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    )
    ->addAttribute(
        'customer',
        'jrewards_quote_after',
        [
            'type' => 'varchar',
            'input' => 'checkbox',
            'label' => 'Gender Preference',
            'global' => 1,
            'visible' => 1,
            'required' => 0,
            'user_defined' => 1,
            'visible_on_front' => 1
        ]
    );

$installer->endSetup();