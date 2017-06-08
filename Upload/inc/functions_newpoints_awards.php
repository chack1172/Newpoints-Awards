<?php
/**
 * Newpoints Awards
 *
 * PHP Version 5
 *
 * @category MyBB_18
 * @package  Newpoints_Awards
 * @author   chack1172 <NBerardozzi@gmail.com>
 * @license  https://creativecommons.org/licenses/by-nc/4.0/ CC BY-NC 4.0
 * @link     http://www.chack1172.altervista.org/Projects/MyBB-18/Newpoints-Awards.html
 */

function load_awards()
{
    global $db;
    $query = $db->simple_select('ougc_awards');
    $awards = [];
    if ($db->num_rows($query) > 0) {
        while ($award = $db->fetch_array($query)) {
            $awards[$award['aid']] = parse_award($award);
        }
    }
    return $awards;
}

function parse_award($award)
{
    global $mybb, $theme;
    $replaces = [
        '{bburl}'   => $mybb->settings['bburl'],
        '{homeurl}' => $mybb->settings['homeurl'],
        '{imgdir}'  => $theme['imgdir']
    ];
    if (is_array($award)) {
        $award['image'] = str_replace(
            array_keys($replaces),
            array_values($replaces),
            $award['image']
        );
    }
    return $award;
}

function activated_ougc_awards()
{
    global $cache;
    $plugins = $cache->read('plugins');
    if (isset($plugins['active']['ougc_awards'])) {
        return true;
    }
    return false;
}

function installed_ougc_awards()
{
    global $db, $cache;
    if (!$db->table_exists('ougc_awards')) {
        newpoints_awards_deactivate();
        $plugins = $cache->read('newpoints_plugins');
        unset($plugins['active']['newpoints_awards']);
        $cache->update('newpoints_plugins', $plugins);
        return false;
    }
    return true;
}

function awards_select($awards, $selected = 0)
{
    global $form;
    $iconlist = '';
    $award_selected = $key = 0;
    foreach ($awards as $award) {
        $iconlist .= <<<EOT
        icons.push({
            'iconFilePath': '{$award['image']}',
            'iconValue': '{$award['aid']}'
        });
EOT;
        if ($selected > 0 && $award['aid'] == $selected) {
            $award_selected = $key;
        }
        $key++;
    }
    $award_list = [];
    foreach ($awards as $award) {
        $award_list[$award['aid']] = $award['name'];
    }
    $code = $form->generate_select_box('award', $award_list, $selected, ['id' => 'award']);
    $code .= <<<EOT
    <div id="award-select" style="display: none"></div>
    <link rel="stylesheet" href="jscripts/iconselect/iconselect.css" type="text/css" />
    <script type="text/javascript" src="jscripts/iconselect/iconselect.js"></script>
    <script type="text/javascript" src="jscripts/iconselect/iscroll.js"></script>
    <script type="text/javascript">
        var iconsSelect,
            icons = [],
            awardInput;
        IconSelect.COMPONENT_ICON_FILE_PATH = 'jscripts/iconselect/arrow.png';
        $(document).ready(function () {
            awardInput = $('#award');
            awardInput.hide();

            $('#award-select').show().on('changed', function (e) {
                awardInput.val(iconsSelect.getSelectedValue());
            });

            iconsSelect = new IconSelect('award-select', {
                'selectedIconWidth': 48,
                'selectedIconHeight': 48,
                'selectedBoxPadding': 5,
                'iconsWidth': 48,
                'iconsHeight': 48,
                'boxIconSpace': 3,
                'vectoralIconNumber': 8,
                'horizontalIconNumber': 1,
                'arrowImagePath': 'jscripts/iconselect/arrow.png'}
            );

            {$iconlist}
            
            iconsSelect.refresh(icons, {$award_selected});
        });
    </script>
EOT;
    return $code;
}
