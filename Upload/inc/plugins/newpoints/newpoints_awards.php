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

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

if (defined('IN_ADMINCP')) {
    newpoints_lang_load('newpoints_awards');
    $plugins->add_hook('newpoints_admin_plugins_deactivate', 'newpoints_awards_destroy');
    $plugins->add_hook('newpoints_admin_plugins_deactivate_commit', 'newpoints_awards_destroy_commit');
    $plugins->add_hook('newpoints_admin_newpoints_menu', 'newpoints_awards_admin_menu');
    $plugins->add_hook('newpoints_admin_newpoints_action_handler', 'newpoints_awards_admin_action_handler');
    $plugins->add_hook('newpoints_admin_newpoints_permissions', 'newpoints_awards_admin_permissions');
} elseif (THIS_SCRIPT == 'newpoints.php') {
    include(MYBB_ROOT . 'inc/functions_newpoints_awards.php');
    if (activated_ougc_awards()) {
        newpoints_lang_load('newpoints_awards');
        $plugins->add_hook('newpoints_default_menu', 'newpoints_awards_menu');
        $plugins->add_hook('newpoints_start', 'newpoints_awards_page');
    }
}

function newpoints_awards_destroy_url($post_code = false)
{
    global $mybb;
    $url = 'index.php?module=newpoints&action=deactivate&uninstall=1&destroy=1&plugin=newpoints_awards';
    if ($post_code) {
        $url .= '&my_post_key='.$mybb->post_code;
    }
    return $url;
}

function newpoints_awards_info()
{
    global $lang;
    $destroy = newpoints_awards_destroy_url(true);
    $code = '<div style="float: right; width: 150px; text-align: center; font-weight: bold">';
    $code .= '<a href="'.$destroy.'" style="color: red">'.$lang->newpoints_awards_destroy.'</a>';
    $code .= '</div>';
    return [
        'name'          => $lang->newpoints_awards_name,
        'description'   => $lang->newpoints_awards_description.$code,
        'website'       => $lang->newpoints_awards_site,
        'author'        => 'chack1172',
        'authorsite'    => $lang->newpoints_awards_authorsite,
        'version'       => '1.0',
        'compatibility' => '2*'
    ];
}

function newpoints_awards_activate()
{
    global $lang;
    include(MYBB_ROOT . 'inc/functions_newpoints_awards.php');
    if (!installed_ougc_awards()) {
        flash_message($lang->newpoints_awards_plugin_missing, 'error');
        admin_redirect('index.php?module=newpoints');
    }
    newpoints_add_template('newpoints_awards', '
<html>
<head>
<title>{$lang->newpoints_awards} - {$lang->newpoints}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
{$inline_errors}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="4"><strong>{$lang->newpoints_awards_plans}</strong></td>
</tr>
<tr>
<td class="tcat" width="70%" colspan="2"><strong>{$lang->newpoints_awards_name}</strong></td>
<td class="tcat" width="15%"><strong>{$lang->newpoints_awards_price}</strong></td>
<td class="tcat" width="15%"><strong>{$lang->newpoints_awards_buy}</strong></td>
</tr>
{$awardlist}
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>');
    newpoints_add_template('newpoints_awards_row', '
<tr>
<td class="{$bgcolor}"><img src="{$award[\'image\']}" alt="" /></td>
<td class="{$bgcolor}" width="70%">{$award[\'name\']}</td>
<td class="{$bgcolor}" width="15%">{$award[\'price\']}</td>
<td class="{$bgcolor}" width="15%">
<form action="newpoints.php" method="POST">
<input type="hidden" name="action" value="buyaward" />
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<input type="hidden" name="aid" value="{$award[\'aid\']}" />
<input type="submit" name="submit" value="{$lang->newpoints_awards_buy}" class="button" />
</form>
</td>
</tr>');
    newpoints_add_template('newpoints_awards_empty', '
<tr>
<td class="trow1" width="100%" colspan="4">{$lang->newpoints_awards_empty}</td>
</tr>');
    newpoints_add_template('newpoints_awards_buy', '
<html>
<head>
<title>{$lang->newpoints_awards_buy} - {$lang->newpoints_awards} - {$lang->newpoints}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
<form action="newpoints.php" method="POST">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}">
<input type="hidden" name="action" value="buyaward" />
<input type="hidden" name="aid" value="{$award[\'aid\']}" />
<input type="hidden" name="buy_confirm" value="1" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="{$colspan}"><strong>{$lang->newpoints_awards_buy_award}</strong></td>
</tr>
<tr>
<td class="trow1">{$lang->newpoints_awards_buy_confirm}</td>
</tr>
<tr>
<td class="tfoot" width="100%" align="center" colspan="{$colspan}">
<input type="submit" name="submit" value="{$lang->newpoints_awards_buy}" class="button" />
</td>
</tr>
</table>
</form>
</td>
</tr>
</table>
{$footer}
</body>
</html>');
}

function newpoints_awards_deactivate()
{
    
}

function newpoints_awards_install()
{
    global $db;
    $collation = $db->build_create_table_collation();
    $db->write_query('CREATE TABLE `'.TABLE_PREFIX.'newpoints_awards` (
        `aid` int UNSIGNED NOT NULL,
        `price` decimal(16,2) NOT NULL DEFAULT \'0\',
        `reason` text NOT NULL,
        `visible` smallint(1) NOT NULL DEFAULT \'1\',
        PRIMARY KEY (`aid`)
    ) ENGINE=MyISAM'.$collation);
}

function newpoints_awards_is_installed()
{
    global $db;
    return $db->table_exists('newpoints_awards');
}

function newpoints_awards_uninstall()
{
    global $db;
    $db->drop_table('newpoints_awards');
}

function newpoints_awards_destroy()
{
    global $mybb, $lang;
    if ($mybb->input['destroy'] == 1) {
        if ($mybb->request_method == 'post') {
            if ($mybb->input['no']) {
                admin_redirect('index.php?module=newpoints');
            }
        } else {
            $form = new Form(newpoints_awards_destroy_url(), 'post');
            echo '<div class="confirm_action">';
            echo '<p>'.$lang->newpoints_awards_destroy_confirm.'</p>';
            echo '<br>';
            echo '<p class="buttons">';
            echo $form->generate_submit_button($lang->yes, ['class' => 'button_yes']);
            echo $form->generate_submit_button($lang->no, ['name' => 'no', 'class' => 'button_no']);
            echo '</p>';
            echo '</div>';
            $form->end();
            exit;
        }
    }
}

function newpoints_awards_destroy_commit()
{
    global $mybb, $lang, $message;
    if ($mybb->input['destroy'] == 1) {
        $files = newpoints_awards_files();
        foreach ($files as $file) {
            newpoints_awards_remove($file);
        }
        $message .= $lang->newpoints_awards_destroyed;
    }
}

function newpoints_awards_remove($file)
{
    if (!empty($file) && file_exists(MYBB_ROOT.$file)) {
        if (is_dir(MYBB_ROOT.$file)) {
            $files = @scandir(MYBB_ROOT.$file);
            if (is_array($files)) {
                foreach ($files as $f) {
                    if ($f == '.' || $f == '..') {
                        continue;
                    }
                    newpoints_awards_remove($file.'/'.$f);
                }
            }
            @rmdir(MYBB_ROOT.$file);
        } else {
            @unlink(MYBB_ROOT.$file);
        }
    }
}

function newpoints_awards_files()
{
    return [
        'admin/jscripts/iconselect',
        'admin/modules/newpoints/awards.php',
        'inc/plugins/newpoints/languages/english/admin/newpoints_awards.lang.php',
        'inc/plugins/newpoints/languages/english/newpoints_awards.lang.php',
        'inc/plugins/newpoints/languages/italiano/admin/newpoints_awards.lang.php',
        'inc/plugins/newpoints/languages/italiano/newpoints_awards.lang.php',
        'inc/plugins/newpoints/newpoints_awards.php',
        'inc/functions_newpoints_awards.php'
    ];
}

function newpoints_awards_admin_menu(&$sub_menu)
{
    global $lang;
    $sub_menu[] = [
        'id'    => 'awards',
        'title' => $lang->newpoints_awards,
        'link'  => 'index.php?module=newpoints-awards'
    ];
}

function newpoints_awards_admin_action_handler(&$actions)
{
    $actions['awards'] = ['active' => 'awards', 'file' => 'awards.php'];
}

function newpoints_awards_admin_permissions(&$admin_permissions)
{
    global $lang;
    $admin_permissions['awards'] = $lang->newpoints_awards_canmanage;
}

function newpoints_awards_menu(&$menu)
{
    global $mybb, $lang;
    if (!$mybb->user['uid']) {
        return;
    }
    $icon = '<a href="'.$mybb->settings['bburl'].'/newpoints.php?action=awards">'.$lang->newpoints_awards.'</a>';
    if (in_array($mybb->input['action'], ['awards', 'buyaward'])) {
        $icon = '&raquo; '.$icon;
    }
    $menu[] = $icon;
}

function newpoints_awards_page()
{
    global $mybb, $db, $templates, $lang, $theme, $options, $inline_errors, $header, $footer, $headerinclude;
    if (!$mybb->user['uid'] || !in_array($mybb->input['action'], ['awards', 'buyaward'])) {
        return;
    }
    $awards = load_awards();
    if ($mybb->input['action'] == 'buyaward' && $mybb->request_method == 'post') {
        verify_post_check($mybb->input['my_post_key']);
        $aid = (int) $mybb->input['aid'];
        if (isset($awards[$aid]) && $awards[$aid]['visible'] == 0) {
            error($lang->newpoints_awards_invalid_award);
        }
        $query = $db->simple_select('newpoints_awards', '*', 'aid='.$aid.' AND visible=1');
        if ($db->num_rows($query) == 0) {
            error($lang->newpoints_awards_invalid_award);
        }
        $award = $db->fetch_array($query);
        $award['name'] = $awards[$aid]['name'];
        $query = $db->simple_select('ougc_awards_users', '*', 'aid='.$aid.' AND uid='.$mybb->user['uid']);
        if ($db->num_rows($query) > 0) {
            error($lang->newpoints_awards_already);
        }
        if ($award['price'] > $mybb->user['newpoints']) {
            error($lang->newpoints_awards_not_enough);
        }
        if ($mybb->input['buy_confirm'] == 1) {
            newpoints_addpoints($mybb->user['uid'], -$award['price']);
            $award['price'] = newpoints_format_points($award['price']);
            $award['reason'] = str_replace('%price%', $award['price'], $award['reason']);
            $db->insert_query('ougc_awards_users', [
                'uid'    => (int) $mybb->user['uid'],
                'aid'    => $aid,
                'reason' => $db->escape_string($award['reason']),
                'date'   => TIME_NOW
            ]);
            redirect(
                'newpoints.php?action=awards',
                $lang->newpoints_awards_bought,
                $lang->newpoints_awards_bought_title
            );
        } else {
            $lang->newpoints_awards_buy_award = $lang->sprintf($lang->newpoints_awards_buy_award, $award['name']);
            eval('$page = "'.$templates->get('newpoints_awards_buy').'";');
        }
    } else {
        $awardlist = '';
        $query = $db->simple_select('newpoints_awards', '*', 'visible=1');
        if ($db->num_rows($query) > 0) {
            while ($award = $db->fetch_array($query)) {
                $aid = $award['aid'];
                if (!isset($awards[$aid]) || $awards[$aid]['visible'] == 0) {
                    continue;
                }
                $bgcolor = alt_trow();
                $award['image'] = $awards[$aid]['image'];
                $award['name'] = $awards[$aid]['name'];
                $award['price'] = newpoints_format_points($award['price']);
                eval('$awardlist .= "'.$templates->get('newpoints_awards_row').'";');
            }
        }
        if ($awardlist == '') {
            eval('$awardlist = "'.$templates->get('newpoints_awards_empty').'";');
        }
        eval('$page = "'.$templates->get('newpoints_awards').'";');
    }
    output_page($page);
}
