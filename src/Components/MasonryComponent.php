<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Masonry\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-masonry
 */

namespace SilverWare\Masonry\Components;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\SelectionGroup;
use SilverStripe\Forms\SelectionGroup_Item;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverWare\Components\BaseListComponent;
use SilverWare\Forms\ViewportsField;

/**
 * An extension of the base component class for a masonry component.
 *
 * @package SilverWare\Masonry\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-masonry
 */
class MasonryComponent extends BaseListComponent
{
    /**
     * Define constants.
     */
    const UNIT_PIXEL   = 'pixel';
    const UNIT_PERCENT = 'percent';
    
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Masonry Component';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Masonry Components';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'A component which shows a masonry layout of images';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware-masonry/admin/client/dist/images/icons/MasonryComponent.png';
    
    /**
     * Defines an ancestor class to hide from the admin interface.
     *
     * @var string
     * @config
     */
    private static $hide_ancestor = BaseListComponent::class;
    
    /**
     * Defines the allowed children for this object.
     *
     * @var array|string
     * @config
     */
    private static $allowed_children = 'none';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'Gutter' => 'AbsoluteInt',
        'ColumnUnit' => 'Varchar(16)',
        'PixelWidth' => 'Viewports',
        'PercentWidth' => 'Viewports',
        'HorizontalOrder' => 'Boolean'
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'Gutter' => 10,
        'ColumnUnit' => 'pixel',
        'ImageItems' => 1,
        'ImageLinksTo' => 'file',
        'HorizontalOrder' => 1
    ];
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Create Style Fields:
        
        $fields->addFieldToTab(
            'Root.Style',
            CompositeField::create([
                SelectionGroup::create(
                    'ColumnUnit',
                    [
                        SelectionGroup_Item::create(
                            self::UNIT_PIXEL,
                            ViewportsField::create(
                                'PixelWidth',
                                ''
                            )->setUseTextInput(true),
                            $this->owner->fieldLabel('Pixels')
                        ),
                        SelectionGroup_Item::create(
                            self::UNIT_PERCENT,
                            ViewportsField::create(
                                'PercentWidth',
                                ''
                            )->setUseTextInput(true),
                            $this->owner->fieldLabel('Percentages')
                        )
                    ]
                )->setTitle($this->owner->fieldLabel('ColumnWidths')),
                TextField::create(
                    'Gutter',
                    $this->fieldLabel('Gutter')
                )
            ])->setName('MasonryComponentStyle')->setTitle($this->i18n_singular_name())
        );
        
        // Create Options Fields:
        
        $fields->addFieldToTab(
            'Root.Options',
            CompositeField::create([
                CheckboxField::create(
                    'HorizontalOrder',
                    $this->fieldLabel('HorizontalOrder')
                )
            ])->setName('MasonryComponentOptions')->setTitle($this->i18n_singular_name())
        );
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers the labels for the fields of the receiver.
     *
     * @param boolean $includerelations Include labels for relations.
     *
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        // Obtain Field Labels (from parent):
        
        $labels = parent::fieldLabels($includerelations);
        
        // Define Field Labels:
        
        $labels['Gutter'] = _t(__CLASS__ . '.GUTTERINPIXELS', 'Gutter (in pixels)');
        $labels['ColumnWidths'] = _t(__CLASS__ . '.WIDTHS', 'Column widths');
        $labels['Percentages'] = _t(__CLASS__ . '.PERCENTAGES', 'Percentages');
        $labels['Pixels'] = _t(__CLASS__ . '.PIXELS', 'Pixels');
        $labels['HorizontalOrder'] = _t(__CLASS__ . '.ORDERITEMSHORIZONTALLY', 'Order items horizontally');
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Answers the configuration for Masonry as an array.
     *
     * @return array
     */
    public function getMasonryConfig()
    {
        $config = [
            'columnWidth' => '.masonry-grid-sizer',
            'itemSelector' => '.masonry-grid-item',
            'percentPosition' => $this->isPercentPosition(),
            'horizontalOrder' => $this->isHorizontalOrder()
        ];
        
        if ($gutter = $this->Gutter) {
            $config['gutter'] = (int) $this->Gutter;
        }
        
        return $config;
    }
    
    /**
     * Answers the configuration for Masonry as a JSON-encoded string.
     *
     * @return string
     */
    public function getMasonryConfigJSON()
    {
        return json_encode($this->getMasonryConfig());
    }
    
    /**
     * Answers an array of grid class names for the HTML template.
     *
     * @return array
     */
    public function getGridClassNames()
    {
        $classes = ['masonry-grid'];
        
        $this->extend('updateGridClassNames', $classes);
        
        return $classes;
    }
    
    /**
     * Answers a list of column width data for the custom CSS template.
     *
     * @return ArrayList
     */
    public function getColumnWidths()
    {
        $data = ArrayList::create();
        
        $widths = $this->getColumnWidthData();
        
        foreach ($widths->getViewports() as $viewport) {
            
            if ($value = $widths->getField($viewport)) {
                
                $data->push(
                    ArrayData::create([
                        'Width' => sprintf('%d%s', $value, $this->ColumnUnitCSS),
                        'Breakpoint' => $widths->getBreakpoint($viewport)
                    ])
                );
                
            }
            
        }
        
        return $data;
    }
    
    /**
     * Answers the column unit for the custom CSS template.
     *
     * @return string
     */
    public function getColumnUnitCSS()
    {
        return ($this->ColumnUnit == self::UNIT_PERCENT) ? '%' : 'px';
    }
    
    /**
     * Answers the appropriate viewports database field for the column unit.
     *
     * @return DBViewports
     */
    public function getColumnWidthData()
    {
        return $this->dbObject($this->ColumnUnit == self::UNIT_PERCENT ? 'PercentWidth' : 'PixelWidth');
    }
    
    /**
     * Answers true if the column unit is a percentage.
     *
     * @return boolean
     */
    public function isPercentPosition()
    {
        return (boolean) ($this->ColumnUnit == self::UNIT_PERCENT);
    }
    
    /**
     * Answers true if the items are ordered horizontally.
     *
     * @return boolean
     */
    public function isHorizontalOrder()
    {
        return (boolean) $this->HorizontalOrder;
    }
}
