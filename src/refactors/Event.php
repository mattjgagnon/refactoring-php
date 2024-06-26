<?php

namespace mattjgagnon\RefactoringPhp\refactors;

require_once __DIR__ . '/events_functions.php';

final class Event
{
    private const empty_lookup_value = '';

    public function events_insert()
    {
        global $Translation;

        // mm: can member insert record?
        $arrPerm = getTablePermissions('events');
        if (!$arrPerm[1]) {
            return FALSE;
        }

        $data['title'] = makeSafe($_REQUEST['title']);
        if ($data['title'] == self::empty_lookup_value) {
            $data['title'] = '';
        }
        $data['date'] = intval($_REQUEST['dateYear']) . '-' . intval($_REQUEST['dateMonth']) . '-' . intval($_REQUEST['dateDay']);
        $data['date'] = parseMySQLDate($data['date'], '');
        $data['status'] = makeSafe($_REQUEST['status']);
        if ($data['status'] == self::empty_lookup_value) {
            $data['status'] = '';
        }
        $data['name_patient'] = makeSafe($_REQUEST['name_patient']);
        if ($data['name_patient'] == self::empty_lookup_value) {
            $data['name_patient'] = '';
        }
        $data['time'] = makeSafe($_REQUEST['time']);
        if ($data['time'] == self::empty_lookup_value) {
            $data['time'] = '';
        }
        $data['time'] = time24($data['time']);
        $data['prescription'] = makeSafe($_REQUEST['prescription']);
        if ($data['prescription'] == self::empty_lookup_value) {
            $data['prescription'] = '';
        }
        $data['diagnosis'] = makeSafe($_REQUEST['diagnosis']);
        if ($data['diagnosis'] == self::empty_lookup_value) {
            $data['diagnosis'] = '';
        }
        $data['comments'] = makeSafe($_REQUEST['comments']);
        if ($data['comments'] == self::empty_lookup_value) {
            $data['comments'] = '';
        }
        if ($data['status'] == '') {
            $echo = StyleSheet() . "\n\n<div class=\"alert alert-danger\">" . $Translation['error:'] . " 'Status': " . $Translation['field not null'] . '<br><br>';
            $echo .= '<a href="" onclick="history.go(-1); return false;">' . $Translation['< back'] . '</a></div>';
            return $echo;
        }
        if ($data['time'] == '') {
            $data['time'] = "12:00";
        }

        // hook: events_before_insert
        if (function_exists('events_before_insert')) {
            $args = [];
            if (!events_before_insert($data, getMemberInfo(), $args)) {
                return FALSE;
            }
        }

        $o = ['silentErrors' => TRUE];
        sql('insert into `events` set       `title`=' . (($data['title'] !== '' && $data['title'] !== NULL) ? "'{$data['title']}'" : 'NULL') . ', `date`=' . (($data['date'] !== '' && $data['date'] !== NULL) ? "'{$data['date']}'" : 'NULL') . ', `status`=' . (($data['status'] !== '' && $data['status'] !== NULL) ? "'{$data['status']}'" : 'NULL') . ', `name_patient`=' . (($data['name_patient'] !== '' && $data['name_patient'] !== NULL) ? "'{$data['name_patient']}'" : 'NULL') . ', `time`=' . (($data['time'] !== '' && $data['time'] !== NULL) ? "'{$data['time']}'" : 'NULL') . ', `prescription`=' . (($data['prescription'] !== '' && $data['prescription'] !== NULL) ? "'{$data['prescription']}'" : 'NULL') . ', `diagnosis`=' . (($data['diagnosis'] !== '' && $data['diagnosis'] !== NULL) ? "'{$data['diagnosis']}'" : 'NULL') . ', `comments`=' . (($data['comments'] !== '' && $data['comments'] !== NULL) ? "'{$data['comments']}'" : 'NULL'), $o);
        if ($o['error'] != '') {
            echo $o['error'];
            echo "<a href=\"events_view.php?addNew_x=1\">{$Translation['< back']}</a>";
            exit;
        }

        $recID = db_insert_id(db_link());

        // hook: events_after_insert
        if (function_exists('events_after_insert')) {
            $res = sql("select * from `events` where `id`='" . makeSafe($recID, FALSE) . "' limit 1", $eo);
            if ($row = db_fetch_assoc($res)) {
                $data = array_map('makeSafe', $row);
            }
            $data['selectedID'] = makeSafe($recID, FALSE);
            $args = [];
            if (!events_after_insert($data, getMemberInfo(), $args)) {
                return $recID;
            }
        }

        // mm: save ownership data
        sql("insert ignore into membership_userrecords set tableName='events', pkValue='" . makeSafe($recID, FALSE) . "', memberID='" . makeSafe(getLoggedMemberID(), FALSE) . "', dateAdded='" . time() . "', dateUpdated='" . time() . "', groupID='" . getLoggedGroupID() . "'", $eo);

        return $recID;
    }

    public function events_delete($selected_id, $AllowDeleteOfParents = FALSE, $skipChecks = FALSE)
    {
        // insure referential integrity ...
        global $Translation;
        $selected_id = makeSafe($selected_id);

        // mm: can member delete record?
        $arrPerm = getTablePermissions('events');
        $ownerGroupID = sqlValue("select groupID from membership_userrecords where tableName='events' and pkValue='$selected_id'");
        $ownerMemberID = sqlValue("select lcase(memberID) from membership_userrecords where tableName='events' and pkValue='$selected_id'");
        if (($arrPerm[4] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm[4] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm[4] == 3) { // allow delete?
            // delete allowed, so continue ...
        } else {
            return $Translation['You don\'t have enough permissions to delete this record'];
        }

        // hook: events_before_delete
        if (function_exists('events_before_delete')) {
            $args = [];
            if (!events_before_delete($selected_id, $skipChecks, getMemberInfo(), $args)) {
                return $Translation['Couldn\'t delete this record'];
            }
        }

        sql("delete from `events` where `id`='$selected_id'", $eo);

        // hook: events_after_delete
        if (function_exists('events_after_delete')) {
            $args = [];
            events_after_delete($selected_id, getMemberInfo(), $args);
        }

        // mm: delete ownership data
        sql("delete from membership_userrecords where tableName='events' and pkValue='$selected_id'", $eo);
    }

    public function events_update($selected_id)
    {
        global $Translation;

        // mm: can member edit record?
        $arrPerm = getTablePermissions('events');
        $ownerGroupID = sqlValue("select groupID from membership_userrecords where tableName='events' and pkValue='" . makeSafe($selected_id) . "'");
        $ownerMemberID = sqlValue("select lcase(memberID) from membership_userrecords where tableName='events' and pkValue='" . makeSafe($selected_id) . "'");
        if (($arrPerm[3] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm[3] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm[3] == 3) { // allow update?
            // update allowed, so continue ...
        } else {
            return FALSE;
        }

        $data['title'] = makeSafe($_REQUEST['title']);
        if ($data['title'] == empty_lookup_value) {
            $data['title'] = '';
        }
        $data['date'] = intval($_REQUEST['dateYear']) . '-' . intval($_REQUEST['dateMonth']) . '-' . intval($_REQUEST['dateDay']);
        $data['date'] = parseMySQLDate($data['date'], '');
        $data['status'] = makeSafe($_REQUEST['status']);
        if ($data['status'] == empty_lookup_value) {
            $data['status'] = '';
        }
        if ($data['status'] == '') {
            echo StyleSheet() . "\n\n<div class=\"alert alert-danger\">{$Translation['error:']} 'Status': {$Translation['field not null']}<br><br>";
            echo '<a href="" onclick="history.go(-1); return false;">' . $Translation['< back'] . '</a></div>';
            exit;
        }
        $data['name_patient'] = makeSafe($_REQUEST['name_patient']);
        if ($data['name_patient'] == empty_lookup_value) {
            $data['name_patient'] = '';
        }
        $data['time'] = makeSafe($_REQUEST['time']);
        if ($data['time'] == empty_lookup_value) {
            $data['time'] = '';
        }
        $data['time'] = time24($data['time']);
        $data['prescription'] = makeSafe($_REQUEST['prescription']);
        if ($data['prescription'] == empty_lookup_value) {
            $data['prescription'] = '';
        }
        $data['diagnosis'] = makeSafe($_REQUEST['diagnosis']);
        if ($data['diagnosis'] == empty_lookup_value) {
            $data['diagnosis'] = '';
        }
        $data['comments'] = makeSafe($_REQUEST['comments']);
        if ($data['comments'] == empty_lookup_value) {
            $data['comments'] = '';
        }
        $data['selectedID'] = makeSafe($selected_id);

        // hook: events_before_update
        if (function_exists('events_before_update')) {
            $args = [];
            if (!events_before_update($data, getMemberInfo(), $args)) {
                return FALSE;
            }
        }

        $o = ['silentErrors' => TRUE];
        sql('update `events` set       `title`=' . (($data['title'] !== '' && $data['title'] !== NULL) ? "'{$data['title']}'" : 'NULL') . ', `date`=' . (($data['date'] !== '' && $data['date'] !== NULL) ? "'{$data['date']}'" : 'NULL') . ', `status`=' . (($data['status'] !== '' && $data['status'] !== NULL) ? "'{$data['status']}'" : 'NULL') . ', `name_patient`=' . (($data['name_patient'] !== '' && $data['name_patient'] !== NULL) ? "'{$data['name_patient']}'" : 'NULL') . ', `time`=' . (($data['time'] !== '' && $data['time'] !== NULL) ? "'{$data['time']}'" : 'NULL') . ', `prescription`=' . (($data['prescription'] !== '' && $data['prescription'] !== NULL) ? "'{$data['prescription']}'" : 'NULL') . ', `diagnosis`=' . (($data['diagnosis'] !== '' && $data['diagnosis'] !== NULL) ? "'{$data['diagnosis']}'" : 'NULL') . ', `comments`=' . (($data['comments'] !== '' && $data['comments'] !== NULL) ? "'{$data['comments']}'" : 'NULL') . " where `id`='" . makeSafe($selected_id) . "'", $o);
        if ($o['error'] != '') {
            echo $o['error'];
            echo '<a href="events_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
            exit;
        }


        // hook: events_after_update
        if (function_exists('events_after_update')) {
            $res = sql("SELECT * FROM `events` WHERE `id`='{$data['selectedID']}' LIMIT 1", $eo);
            if ($row = db_fetch_assoc($res)) {
                $data = array_map('makeSafe', $row);
            }
            $data['selectedID'] = $data['id'];
            $args = [];
            if (!events_after_update($data, getMemberInfo(), $args)) {
                return;
            }
        }

        // mm: update ownership data
        sql("update membership_userrecords set dateUpdated='" . time() . "' where tableName='events' and pkValue='" . makeSafe($selected_id) . "'", $eo);

    }

    public function events_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0, $TemplateDV = '', $TemplateDVP = '')
    {
        // function to return an editable form for a table records
        // and fill it with data of record whose ID is $selected_id. If $selected_id
        // is empty, an empty form is shown, with only an 'Add New'
        // button displayed.

        global $Translation;

        // mm: get table permissions
        $arrPerm = getTablePermissions('events');
        if (!$arrPerm[1] && $selected_id == '') {
            return '';
        }
        $AllowInsert = ($arrPerm[1] ? TRUE : FALSE);
        // print preview?
        $dvprint = FALSE;
        if ($selected_id && $_REQUEST['dvprint_x'] != '') {
            $dvprint = TRUE;
        }

        $filterer_name_patient = thisOr(undo_magic_quotes($_REQUEST['filterer_name_patient']), '');

        // populate filterers, starting from children to grand-parents

        // unique random identifier
        $rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
        // combobox: date
        $combo_date = new DateCombo;
        $combo_date->DateFormat = "mdy";
        $combo_date->MinYear = 1900;
        $combo_date->MaxYear = 2100;
        $combo_date->DefaultDate = parseMySQLDate('', '');
        $combo_date->MonthNames = $Translation['month names'];
        $combo_date->NamePrefix = 'date';
        // combobox: status
        $combo_status = new Combo;
        $combo_status->ListType = 2;
        $combo_status->MultipleSeparator = ', ';
        $combo_status->ListBoxHeight = 10;
        $combo_status->RadiosPerLine = 1;
        if (is_file(dirname(__FILE__) . '/hooks/events.status.csv')) {
            $status_data = addslashes(implode('', @file(dirname(__FILE__) . '/hooks/events.status.csv')));
            $combo_status->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions($status_data)));
            $combo_status->ListData = $combo_status->ListItem;
        } else {
            $combo_status->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions("Active;;Cancelled")));
            $combo_status->ListData = $combo_status->ListItem;
        }
        $combo_status->SelectName = 'status';
        $combo_status->AllowNull = FALSE;
        // combobox: name_patient
        $combo_name_patient = new DataCombo;

        if ($selected_id) {
            // mm: check member permissions
            if (!$arrPerm[2]) {
                return "";
            }
            // mm: who is the owner?
            $ownerGroupID = sqlValue("select groupID from membership_userrecords where tableName='events' and pkValue='" . makeSafe($selected_id) . "'");
            $ownerMemberID = sqlValue("select lcase(memberID) from membership_userrecords where tableName='events' and pkValue='" . makeSafe($selected_id) . "'");
            if ($arrPerm[2] == 1 && getLoggedMemberID() != $ownerMemberID) {
                return "";
            }
            if ($arrPerm[2] == 2 && getLoggedGroupID() != $ownerGroupID) {
                return "";
            }

            // can edit?
            if (($arrPerm[3] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm[3] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm[3] == 3) {
                $AllowUpdate = 1;
            } else {
                $AllowUpdate = 0;
            }

            $res = sql("select * from `events` where `id`='" . makeSafe($selected_id) . "'", $eo);
            if (!($row = db_fetch_array($res))) {
                return error_message($Translation['No records found'], 'events_view.php', FALSE);
            }
            $urow = $row; /* unsanitized data */
            $hc = new CI_Input();
            $row = $hc->xss_clean($row); /* sanitize data */
            $combo_date->DefaultDate = $row['date'];
            $combo_status->SelectedData = $row['status'];
            $combo_name_patient->SelectedData = $row['name_patient'];
        } else {
            $combo_status->SelectedText = ($_REQUEST['FilterField'][1] == '4' && $_REQUEST['FilterOperator'][1] == '<=>' ? (get_magic_quotes_gpc() ? stripslashes($_REQUEST['FilterValue'][1]) : $_REQUEST['FilterValue'][1]) : "");
            $combo_name_patient->SelectedData = $filterer_name_patient;
        }
        $combo_status->Render();
        $combo_name_patient->HTML = '<span id="name_patient-container' . $rnd1 . '"></span><input type="hidden" name="name_patient" id="name_patient' . $rnd1 . '" value="' . html_attr($combo_name_patient->SelectedData) . '">';
        $combo_name_patient->MatchText = '<span id="name_patient-container-readonly' . $rnd1 . '"></span><input type="hidden" name="name_patient" id="name_patient' . $rnd1 . '" value="' . html_attr($combo_name_patient->SelectedData) . '">';

        ob_start();
        ?>

        <script>
            // initial lookup values
            AppGini.current_name_patient__RAND__ = {
                text: "",
                value: "<?php echo addslashes($selected_id ? $urow['name_patient'] : $filterer_name_patient); ?>"
            };

            jQuery(function () {
                setTimeout(function () {
                    if (typeof (name_patient_reload__RAND__) == 'function') name_patient_reload__RAND__();
                }, 10); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
            });

            function name_patient_reload__RAND__() {
                <?php if(($AllowUpdate || $AllowInsert) && !$dvprint){ ?>

                $j("#name_patient-container__RAND__").select2({
                    /* initial default value */
                    initSelection: function (e, c) {
                        $j.ajax({
                            url: 'ajax_combo.php',
                            dataType: 'json',
                            data: {id: AppGini.current_name_patient__RAND__.value, t: 'events', f: 'name_patient'},
                            success: function (resp) {
                                c({
                                    id: resp.results[0].id,
                                    text: resp.results[0].text
                                });
                                $j('[name="name_patient"]').val(resp.results[0].id);
                                $j('[id=name_patient-container-readonly__RAND__]').html('<span id="name_patient-match-text">' + resp.results[0].text + '</span>');
                                if (resp.results[0].id == '<?php echo empty_lookup_value; ?>') {
                                    $j('.btn[id=patients_view_parent]').hide();
                                } else {
                                    $j('.btn[id=patients_view_parent]').show();
                                }


                                if (typeof (name_patient_update_autofills__RAND__) == 'function') name_patient_update_autofills__RAND__();
                            }
                        });
                    },
                    width: ($j('fieldset .col-xs-11').width() - select2_max_width_decrement()) + 'px',
                    formatNoMatches: function (term) {
                        return '<?php echo addslashes($Translation['No matches found!']); ?>';
                    },
                    minimumResultsForSearch: 10,
                    loadMorePadding: 200,
                    ajax: {
                        url: 'ajax_combo.php',
                        dataType: 'json',
                        cache: true,
                        data: function (term, page) {
                            return {s: term, p: page, t: 'events', f: 'name_patient'};
                        },
                        results: function (resp, page) {
                            return resp;
                        }
                    },
                    escapeMarkup: function (str) {
                        return str;
                    }
                }).on('change', function (e) {
                    AppGini.current_name_patient__RAND__.value = e.added.id;
                    AppGini.current_name_patient__RAND__.text = e.added.text;
                    $j('[name="name_patient"]').val(e.added.id);
                    if (e.added.id == '<?php echo empty_lookup_value; ?>') {
                        $j('.btn[id=patients_view_parent]').hide();
                    } else {
                        $j('.btn[id=patients_view_parent]').show();
                    }


                    if (typeof (name_patient_update_autofills__RAND__) == 'function') name_patient_update_autofills__RAND__();
                });

                if (!$j("#name_patient-container__RAND__").length) {
                    $j.ajax({
                        url: 'ajax_combo.php',
                        dataType: 'json',
                        data: {id: AppGini.current_name_patient__RAND__.value, t: 'events', f: 'name_patient'},
                        success: function (resp) {
                            $j('[name="name_patient"]').val(resp.results[0].id);
                            $j('[id=name_patient-container-readonly__RAND__]').html('<span id="name_patient-match-text">' + resp.results[0].text + '</span>');
                            if (resp.results[0].id == '<?php echo empty_lookup_value; ?>') {
                                $j('.btn[id=patients_view_parent]').hide();
                            } else {
                                $j('.btn[id=patients_view_parent]').show();
                            }

                            if (typeof (name_patient_update_autofills__RAND__) == 'function') name_patient_update_autofills__RAND__();
                        }
                    });
                }

                <?php }else{ ?>

                $j.ajax({
                    url: 'ajax_combo.php',
                    dataType: 'json',
                    data: {id: AppGini.current_name_patient__RAND__.value, t: 'events', f: 'name_patient'},
                    success: function (resp) {
                        $j('[id=name_patient-container__RAND__], [id=name_patient-container-readonly__RAND__]').html('<span id="name_patient-match-text">' + resp.results[0].text + '</span>');
                        if (resp.results[0].id == '<?php echo empty_lookup_value; ?>') {
                            $j('.btn[id=patients_view_parent]').hide();
                        } else {
                            $j('.btn[id=patients_view_parent]').show();
                        }

                        if (typeof (name_patient_update_autofills__RAND__) == 'function') name_patient_update_autofills__RAND__();
                    }
                });
                <?php } ?>

            }
        </script>
        <?php

        $lookups = str_replace('__RAND__', $rnd1, ob_get_contents());
        ob_end_clean();


        // code for template based detail view forms

        // open the detail view template
        if ($dvprint) {
            $template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/events_templateDVP.html';
            $templateCode = @file_get_contents($template_file);
        } else {
            $template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/events_templateDV.html';
            $templateCode = @file_get_contents($template_file);
        }

        // process form title
        $templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Event details', $templateCode);
        $templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
        $templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
        // process buttons
        if ($AllowInsert) {
            if (!$selected_id) {
                $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return events_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
            }
            $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return events_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
        } else {
            $templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
        }

        // 'Back' button action
        if ($_REQUEST['Embedded']) {
            $backAction = 'window.parent.jQuery(\'.modal\').modal(\'hide\'); return false;';
        } else {
            $backAction = '$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
        }

        if ($selected_id) {
            if (!$_REQUEST['Embedded']) {
                $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$$(\'form\')[0].writeAttribute(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
            }
            if ($AllowUpdate) {
                $templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return events_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
            } else {
                $templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
            }
            if (($arrPerm[4] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm[4] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm[4] == 3) { // allow delete?
                $templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
            } else {
                $templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
            }
            $templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
        } else {
            $templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
            $templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
            $templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
        }

        // set records to read only if user can't insert new records and can't edit current record
        if (($selected_id && !$AllowUpdate && !$AllowInsert) || (!$selected_id && !$AllowInsert)) {
            $jsReadOnly .= "\tjQuery('#title').replaceWith('<div class=\"form-control-static\" id=\"title\">' + (jQuery('#title').val() || '') + '</div>');\n";
            $jsReadOnly .= "\tjQuery('#date').prop('readonly', true);\n";
            $jsReadOnly .= "\tjQuery('#dateDay, #dateMonth, #dateYear').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
            $jsReadOnly .= "\tjQuery('input[name=status]').parent().html('<div class=\"form-control-static\">' + jQuery('input[name=status]:checked').next().text() + '</div>')\n";
            $jsReadOnly .= "\tjQuery('#name_patient').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
            $jsReadOnly .= "\tjQuery('#name_patient_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
            $jsReadOnly .= "\tjQuery('#time').replaceWith('<div class=\"form-control-static\" id=\"time\">' + (jQuery('#time').val() || '') + '</div>');\n";
            $jsReadOnly .= "\tjQuery('#prescription').replaceWith('<div class=\"form-control-static\" id=\"prescription\">' + (jQuery('#prescription').val() || '') + '</div>');\n";
            $jsReadOnly .= "\tjQuery('#diagnosis').replaceWith('<div class=\"form-control-static\" id=\"diagnosis\">' + (jQuery('#diagnosis').val() || '') + '</div>');\n";
            $jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

            $noUploads = TRUE;
        } elseif ($AllowInsert) {
            $jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
            $jsEditable .= "\tjQuery('#time').addClass('always_shown').timepicker({ defaultTime: false, showSeconds: true, showMeridian: true, showInputs: false, disableFocus: true, minuteStep: 5 });";
            $jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
        }

        // process combos
        $templateCode = str_replace('<%%COMBO(date)%%>', ($selected_id && !$arrPerm[3] ? '<div class="form-control-static">' . $combo_date->GetHTML(TRUE) . '</div>' : $combo_date->GetHTML()), $templateCode);
        $templateCode = str_replace('<%%COMBOTEXT(date)%%>', $combo_date->GetHTML(TRUE), $templateCode);
        $templateCode = str_replace('<%%COMBO(status)%%>', $combo_status->HTML, $templateCode);
        $templateCode = str_replace('<%%COMBOTEXT(status)%%>', $combo_status->SelectedData, $templateCode);
        $templateCode = str_replace('<%%COMBO(name_patient)%%>', $combo_name_patient->HTML, $templateCode);
        $templateCode = str_replace('<%%COMBOTEXT(name_patient)%%>', $combo_name_patient->MatchText, $templateCode);
        $templateCode = str_replace('<%%URLCOMBOTEXT(name_patient)%%>', urlencode($combo_name_patient->MatchText), $templateCode);

        /* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
        $lookup_fields = [
            'name_patient' => [
                'patients',
                'Patient Name',
            ],
        ];
        foreach ($lookup_fields as $luf => $ptfc) {
            $pt_perm = getTablePermissions($ptfc[0]);

            // process foreign key links
            if ($pt_perm['view'] || $pt_perm['edit']) {
                $templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-md" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
            }

            // if user has insert permission to parent table of a lookup field, put an add new button
            if ($pt_perm['insert'] && !$_REQUEST['Embedded']) {
                $templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent hspacer-md" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
            }
        }

        // process images
        $templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(title)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(date)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(status)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(name_patient)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(time)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(prescription)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(diagnosis)%%>', '', $templateCode);
        $templateCode = str_replace('<%%UPLOADFILE(comments)%%>', '', $templateCode);

        // process values
        if ($selected_id) {
            $templateCode = str_replace('<%%VALUE(id)%%>', html_attr($row['id']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
            $templateCode = str_replace('<%%VALUE(title)%%>', html_attr($row['title']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode($urow['title']), $templateCode);
            $templateCode = str_replace('<%%VALUE(date)%%>', @date('m/d/Y', @strtotime(html_attr($row['date']))), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(date)%%>', urlencode(@date('m/d/Y', @strtotime(html_attr($urow['date'])))), $templateCode);
            $templateCode = str_replace('<%%VALUE(status)%%>', html_attr($row['status']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(status)%%>', urlencode($urow['status']), $templateCode);
            $templateCode = str_replace('<%%VALUE(name_patient)%%>', html_attr($row['name_patient']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(name_patient)%%>', urlencode($urow['name_patient']), $templateCode);
            $templateCode = str_replace('<%%VALUE(time)%%>', time12(html_attr($row['time'])), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(time)%%>', urlencode(time12($urow['time'])), $templateCode);
            $templateCode = str_replace('<%%VALUE(prescription)%%>', html_attr($row['prescription']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(prescription)%%>', urlencode($urow['prescription']), $templateCode);
            $templateCode = str_replace('<%%VALUE(diagnosis)%%>', html_attr($row['diagnosis']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(diagnosis)%%>', urlencode($urow['diagnosis']), $templateCode);
            if ($AllowUpdate || $AllowInsert) {
                $templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<textarea name="comments" id="comments" rows="5">' . html_attr($row['comments']) . '</textarea>', $templateCode);
            } else {
                $templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<div id="comments" class="form-control-static">' . $row['comments'] . '</div>', $templateCode);
            }
            $templateCode = str_replace('<%%VALUE(comments)%%>', nl2br($row['comments']), $templateCode);
            $templateCode = str_replace('<%%URLVALUE(comments)%%>', urlencode($urow['comments']), $templateCode);
        } else {
            $templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(title)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(date)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(date)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(status)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(status)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(name_patient)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(name_patient)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(time)%%>', '12:00', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(time)%%>', urlencode('12:00'), $templateCode);
            $templateCode = str_replace('<%%VALUE(prescription)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(prescription)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%VALUE(diagnosis)%%>', '', $templateCode);
            $templateCode = str_replace('<%%URLVALUE(diagnosis)%%>', urlencode(''), $templateCode);
            $templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<textarea name="comments" id="comments" rows="5"></textarea>', $templateCode);
        }

        // process translations
        foreach ($Translation as $symbol => $trans) {
            $templateCode = str_replace("<%%TRANSLATION($symbol)%%>", $trans, $templateCode);
        }

        // clear scrap
        $templateCode = str_replace('<%%', '<!-- ', $templateCode);
        $templateCode = str_replace('%%>', ' -->', $templateCode);

        // hide links to inaccessible tables
        if ($_REQUEST['dvprint_x'] == '') {
            $templateCode .= "\n\n<script>\$j(function(){\n";
            $arrTables = getTableList();
            foreach ($arrTables as $name => $caption) {
                $templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
                $templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
            }

            $templateCode .= $jsReadOnly;
            $templateCode .= $jsEditable;

            if (!$selected_id) {
            }

            $templateCode .= "\n});</script>\n";
        }

        // ajaxed auto-fill fields
        $templateCode .= '<script>';
        $templateCode .= '$j(function() {';


        $templateCode .= "});";
        $templateCode .= "</script>";
        $templateCode .= $lookups;

        // handle enforced parent values for read-only lookup fields

        // don't include blank images in lightbox gallery
        $templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

        // don't display empty email links
        $templateCode = preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

        /* default field values */
        $rdata = $jdata = get_defaults('events');
        if ($selected_id) {
            $jdata = get_joined_record('events', $selected_id);
            $rdata = $row;
        }
        $cache_data = [
            'rdata' => array_map('nl2br', array_map('addslashes', $rdata)),
            'jdata' => array_map('nl2br', array_map('addslashes', $jdata)),
        ];
        $templateCode .= loadView('events-ajax-cache', $cache_data);

        // hook: events_dv
        if (function_exists('events_dv')) {
            $args = [];
            events_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
        }

        return $templateCode;
    }
}