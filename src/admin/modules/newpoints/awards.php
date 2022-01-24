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

if (!defined('IN_ADMINCP')) {
    die('Direct initialization of this file is not allowed.');
}

include(MYBB_ROOT . 'inc/functions_newpoints_awards.php');
if (!installed_ougc_awards()) {
    flash_message($lang->newpoints_awards_plugin_missing, 'error');
    admin_redirect('index.php?module=newpoints');
}
$awards = load_awards();

$sub_tabs['newpoints_awards'] = [
    'title'       => $lang->newpoints_awards,
    'link'        => 'index.php?module=newpoints-awards',
    'description' => $lang->newpoints_awards_desc
];
$sub_tabs['newpoints_awards_add'] = [
    'title'       => $lang->newpoints_awards_add,
    'link'        => 'index.php?module=newpoints-awards&action=add',
    'description' => $lang->newpoints_awards_add_desc
];

$page->add_breadcrumb_item($lang->newpoints, 'index.php?module=newpoints');
$page->add_breadcrumb_item($lang->newpoints_awards, 'index.php?module=newpoints-awards');
if ($mybb->input['action'] == 'delete') {
    $aid = (int) $mybb->input['aid'];
    $page->add_breadcrumb_item($lang->newpoints_awards_delete, 'index.php?module=newpoints-awards&action=delete');
    if ($mybb->request_method == 'post') {
        if ($mybb->input['no']) {
            admin_redirect('index.php?module=newpoints-awards');
        }
        $db->delete_query('newpoints_awards', 'aid='.$aid);
        flash_message($lang->newpoints_awards_award_deleted, 'success');
        admin_redirect('index.php?module=newpoints-awards');
    } else {
        $page->output_confirm_action(
            'index.php?module=newpoints-awards&action=delete&aid='.$aid,
            $lang->newpoints_awards_confirm_deleteaward
        );
    }
} elseif ($mybb->input['action'] == 'edit') {
    $aid = (int) $mybb->input['aid'];
    $query = $db->simple_select('newpoints_awards', '*', 'aid='.$aid);
    if ($db->num_rows($query) == 0) {
        admin_redirect('index.php?module=newpoints-awards&action=add&aid='.$aid);
    } else {
        $award = $db->fetch_array($query);
    }
    if ($mybb->request_method == 'post') {
        $new_aid = (int) $mybb->input['award'];
        $query = $db->simple_select('ougc_awards', '*', 'aid='.$new_aid);
        if ($db->num_rows($query) == 0) {
            $errors[] = $lang->newpoints_awards_invalid_award;
        } elseif ($aid != $new_aid) {
            $query = $db->simple_select('newpoints_awards', '*', 'aid='.$new_aid);
            if ($db->num_rows($query) > 0) {
                $errors[] = $lang->newpoints_awards_awardprice_exists;
            }
        }
        if (empty($errors)) {
            $db->update_query('newpoints_awards', [
                'aid'     => $new_aid,
                'price'   => (float) $mybb->input['price'],
                'reason'  => $db->escape_string(trim($mybb->input['reason'])),
                'visible' => (int) $mybb->input['visible']
            ], 'aid='.$aid);
            flash_message($lang->newpoints_awards_award_edited, 'success');
            admin_redirect('index.php?module=newpoints-awards');
        }
    }
    $sub_tabs['newpoints_awards_edit'] = [
        'title'       => $lang->newpoints_awards_edit,
        'link'        => 'index.php?module=newpoints-awards&action=edit&aid='.$aid,
        'description' => $lang->newpoints_awards_edit_desc
    ];
    $page->add_breadcrumb_item($lang->newpoints_awards_edit, 'index.php?module=newpoints-awards&action=edit&aid='.$aid);
    $page->output_header($lang->newpoints_awards_editaward);
    $page->output_nav_tabs($sub_tabs, 'newpoints_awards_edit');
    if (count($awards) == 0) {
        flash_message($lang->newpoints_awards_no_awards, 'error');
        admin_redirect('index.php?module=newpoints-awards');
    }
    if (count($errors) > 0) {
        $page->output_inline_error($errors);
    }
    $form = new Form('index.php?module=newpoints-awards&amp;action=edit&amp;aid='.$aid, 'post', 'newpoints_awards');
    $form_container = new FormContainer($lang->newpoints_awards_editaward);
    $form_container->output_row(
        $lang->newpoints_awards_award,
        $lang->newpoints_awards_award_desc,
        awards_select($awards, $aid)
    );
    $form_container->output_row(
        $lang->newpoints_awards_price,
        $lang->newpoints_awards_price_desc,
        $form->generate_text_box('price', $award['price'])
    );
    $form_container->output_row(
        $lang->newpoints_awards_reason,
        $lang->newpoints_awards_reason_desc,
        $form->generate_text_area('reason', $award['reason'])
    );
    $form_container->output_row(
        $lang->newpoints_awards_visible,
        $lang->newpoints_awards_visible_desc,
        $form->generate_yes_no_radio('visible', $award['visible'])
    );
    $form_container->end();
    $form->output_submit_wrapper([
        $form->generate_submit_button($lang->newpoints_awards_submit),
        $form->generate_reset_button($lang->newpoints_awards_reset),
    ]);
    $form->end();
    $page->output_footer();
} elseif ($mybb->input['action'] == 'add') {
    if ($mybb->request_method == 'post') {
        $award = (int) $mybb->input['award'];
        $query = $db->simple_select('ougc_awards', '*', 'aid='.$award);
        if ($db->num_rows($query) == 0) {
            $errors[] = $lang->newpoints_awards_invalid_award;
        } else {
            $query = $db->simple_select('newpoints_awards', '*', 'aid='.$award);
            if ($db->num_rows($query) > 0) {
                $errors[] = $lang->newpoints_awards_awardprice_exists;
            }
        }
        if (empty($errors)) {
            $db->insert_query('newpoints_awards', [
                'aid'     => $award,
                'price'   => (float) $mybb->input['price'],
                'reason'  => $db->escape_string(trim($mybb->input['reason'])),
                'visible' => (int) $mybb->input['visible']
            ]);
            flash_message($lang->newpoints_awards_award_added, 'success');
            admin_redirect('index.php?module=newpoints-awards');
        }
    }
    if (count($awards) == 0) {
        flash_message($lang->newpoints_awards_no_awards, 'error');
        admin_redirect('index.php?module=newpoints-awards');
    }
    $aid = (int) $mybb->input['aid'];
    $page->add_breadcrumb_item($lang->newpoints_awards_add, 'index.php?module=newpoints-awards&action=add');
    $page->output_header($lang->newpoints_awards_addaward);
    $page->output_nav_tabs($sub_tabs, 'newpoints_awards_add');
    if (count($errors) > 0) {
        $page->output_inline_error($errors);
    }
    $form = new Form('index.php?module=newpoints-awards&amp;action=add', 'post', 'newpoints_awards');
    $form_container = new FormContainer($lang->newpoints_awards_addaward);
    $form_container->output_row(
        $lang->newpoints_awards_award,
        $lang->newpoints_awards_award_desc,
        awards_select($awards, $aid)
    );
    $form_container->output_row(
        $lang->newpoints_awards_price,
        $lang->newpoints_awards_price_desc,
        $form->generate_text_box('price', '0.00')
    );
    $form_container->output_row(
        $lang->newpoints_awards_reason,
        $lang->newpoints_awards_reason_desc,
        $form->generate_text_area('reason', '')
    );
    $form_container->output_row(
        $lang->newpoints_awards_visible,
        $lang->newpoints_awards_visible_desc,
        $form->generate_yes_no_radio('visible', '1')
    );
    $form_container->end();
    $form->output_submit_wrapper([
        $form->generate_submit_button($lang->newpoints_awards_submit),
        $form->generate_reset_button($lang->newpoints_awards_reset),
    ]);
    $form->end();
    $page->output_footer();
}

if (!$mybb->input['action']) {
    $page->output_header($lang->newpoints_awards);
    $page->output_nav_tabs($sub_tabs, 'newpoints_awards');

    $table = new Table;
    $table->construct_header(
        $lang->newpoints_awards_award,
        ['width' => '60%', 'colspan' => 2]
    );
    $table->construct_header(
        $lang->newpoints_awards_price,
        ['width' => '20%', 'class' => 'align_center']
    );
    $table->construct_header(
        $lang->controls,
        ['width' => '20%', 'class' => 'align_center']
    );

    $query = $db->simple_select('newpoints_awards', '*');
    while ($award = $db->fetch_array($query)) {
        $extra = '';
        if (!isset($awards[$award['aid']])) {
            $db->delete_query('newpoints_awards', 'aid='.$award['aid']);
            continue;
        }
        if ($award['visible'] == 1) {
            $status_img = 'bullet_on.png';
            $status_txt = $lang->newpoints_awards_alt_visible;
        } else {
            $status_img = 'bullet_off.png';
            $status_txt = $lang->newpoints_awards_alt_hidden;
        }
        $icon = '<img src="styles/'.$page->style.'/images/icons/'.$status_img.'" alt="('.$status_txt.')" title="'.$status_txt.'" style="vertical-align: middle">';
        $table->construct_cell(
            '<img src="'.$awards[$award['aid']]['image'].'" alt="">',
            ['width' => '1%', 'class' => 'align_center']
        );
        $table->construct_cell(
            $icon.' '.$awards[$award['aid']]['name'],
            ['width' => '59%']
        );
        $table->construct_cell(
            newpoints_format_points($award['price']),
            ['class' => 'align_center']
        );
        $popup = new PopupMenu('field_'.$award['aid'], $lang->options);
        $popup->add_item($lang->edit, 'index.php?module=newpoints-awards&amp;action=edit&amp;aid='.$award['aid']);
        $popup->add_item($lang->delete, 'index.php?module=newpoints-awards&amp;action=delete&amp;aid='.$award['aid']);
        $table->construct_cell(
            $popup->fetch(),
            ['class' => 'align_center']
        );
        $table->construct_row();
    }

    if ($table->num_rows() == 0) {
        $table->construct_cell(
            $lang->newpoints_awards_no_awards_price,
            ['colspan' => 4]
        );
        $table->construct_row();
    }

    $table->output($lang->newpoints_awards);

    $page->output_footer();
}
