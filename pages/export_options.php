<?php require( 'header.php' ); ?>
<?php
    // Validate the job_id
    $job_id = $_GET['job_id'];
    if ( $hcpp->quickstart->is_job_valid( $job_id ) === false ) {
        header( 'Location: ?quickstart=main' );
        exit;
    }

    // Get the manifest
    $manifest = $hcpp->quickstart->get_job_data( $job_id, 'manifest' );
    if ( $manifest === false ) {
        header( 'Location: ?quickstart=main' );
        exit;
    }

    // Get the selected databases
    $dbs = [];
    if ( !isset( $_GET['dbs'] ) ) {
        header( 'Location: ?quickstart=main' );
        exit;
    }else{
        $dbs = $_GET['dbs'];
    }

    // Cull unselected databases from the manifest
    $db_details = $manifest['databases'];
    $db_selected = [];
    foreach ( $db_details as $db ) {
        if ( strpos( $dbs, $db['DATABASE'] ) === false ) {
            continue;
        }
        $db_selected[] = $db;
    }
    $manifest['databases'] = $db_selected;
    $hcpp->set_job_data( $job_id, 'manifest', $manifest );
    $domain = $manifest['domain'];
?>
<form id="export-options-form" method="post" action="?quickstart=export_now&domain=<?php echo $domain; ?>&dbs=<?php echo $dbs; ?>&job_id=<?php echo $job_id; ?>">
    <div class="toolbar">
        <div class="toolbar-inner">
            <div class="toolbar-buttons">
                <a href="?quickstart=export_details&domain=<?php echo $domain; ?>&dbs=<?php echo $dbs; ?>&job_id=<?php echo $job_id; ?>" class="button button-secondary button-back js-button-back" id="back-button">
                    <i tabindex="300" class="fas fa-arrow-left icon-blue"></i>Back			
                </a>
            </div>
            <div class="toolbar-buttons">
                <button tabindex="200" class="button" type="submit" id="continue-button"><i class="fas fa-arrow-right icon-blue"></i>Continue</button>
            </div>
        </div>
    </div>
    <div class="body-reset container">
        <div class="quickstart qs_export_options">
            <h1>Export Options</h1>
            <legend>Leave all items checked for default export options.</legend>
            <p>
                <input class="export_option" type="checkbox" id="cgi_bin" checked="checked" tabindex="100"/>
                <label for="cgi_bin">Include ./cgi-bin folder.</label>
            </p>
            <p>
                <input class="export_option" type="checkbox" id="document_errors" checked="checked" tabindex="100"/>
                <label for="document_errors">Include ./document_errors folder.</label>
            </p>
            <p>
                <input class="export_option" type="checkbox" id="nodeapp" checked="checked" tabindex="100"/>
                <label for="nodeapp">Include ./nodeapp folder.</label>
            </p>
            <p>
                <input class="export_option" type="checkbox" id="private" checked="checked" tabindex="100"/>
                <label for="private">Include ./private folder.</label>
            </p>
            <p>
                <input class="export_option" type="checkbox" id="public_html" checked="checked" tabindex="100"/>
                <label for="public_html">Include ./public_html folder.</label>
            </p>
            <p>
                <input class="export_option" type="checkbox" id="exvc" checked="checked" tabindex="100"/>
                <label for="exvc">Exclude version control files &amp; folders (.git*, .svn, .hg).</label>
            </p>
            <br>
            <h3 tabindex="100"><i class="fas fa-caret-right"></i> Advanced Options</h3>
            <div id="advanced-opt" style="display:none;">
                <p>
                    Additional search and replace controls to appear when importing. Use these 
                    to replace strings in the database and files; allowing user customizations.
                </p>
                <p>
                    Learn more on <a href="https://devstia.com/docs/export-advanced-options" target="_blank">Devstia's website</a>.
                </p>
                <br>
                <table id="advanced-options-table" class="units-table js-units-container">
                    <thead id="adv-options-table" class="units-table-header">
                        <tr class="units-table-row">
                            <th class="units-table-cell">Label</th>
                            <th class="units-table-cell">Value</th>
                            <th class="units-table-cell">Ref. Files</th>
                            <th class="units-table-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php


                            // Pre-populate the table with any existing advanced options if private/devstia_manifest.json exists
                            $private_folder = "/home/" . $_SESSION['user'] . "/web/" . $domain . "/private";
                            if ( file_exists( $private_folder . '/devstia_manifest.json' ) ) {
                                try {
                                    $content = file_get_contents( $private_folder . '/devstia_manifest.json' );
                                    $json = json_decode( $content, true );
                                    foreach ( $json['export_adv_options'] as $option ) {
                                        $label = htmlspecialchars( $option['label'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                                        $value = htmlspecialchars( $option['value'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                                        $ref_files = htmlspecialchars( implode( "\n", $option['ref_files'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                                        echo '<tr class="units-table-row">';
                                        echo '    <td class="units-table-cell">' . $label . '</td>';
                                        echo '    <td class="units-table-cell">' . $value . '</td>';
                                        echo '    <td class="units-table-cell">' . $ref_files . '</td>';
                                        echo '    <td class="units-table-cell adv-trash"><span tabindex="100" class="delete-row-button"><i class="fas fa-trash"></i> Delete</span></td>';
                                        echo '</tr>';
                                    }
                                }catch (Exception $e) {
                                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                                }
                            }
                        ?>
                    </tbody>
                </table>
                <br>
                <form id="add-row-form">
                    <div class="u-mb10">
                        <label for="label-input" class="form-label">
                            Label
                        </label>
                        <input type="text" class="form-control" name="label-input" id="label-input" placeholder="Label" tabindex="100">
                    </div>
                    <div class="u-mb10">
                        <label for="value-input" class="form-label">
                            Value
                        </label>
                        <textarea class="form-control" name="value-input" id="value-input" placeholder="Value" tabindex="100"></textarea>
                    </div>
                    <div class="u-mb10">
                        <label for="ref-files-input" class="form-label">
                            Reference Files
                        </label>
                        <textarea class="form-control" name="ref-files-input" id="ref-files-input" placeholder="./public_html/index.html" tabindex="100"></textarea>
                    </div>
                    <button class="button" type="button" id="add-row-button">
                        <i tabindex="100" class="fas fa-plus icon-blue"></i>Add
                    </button>
                </form>
            </div>
        </div>
        <input type="hidden" id="export_options" name="export_options">
        <input type="hidden" id="export_adv_options" name="export_adv_options">   
    </div>
</form>
<script>
    (function($){
        $(function() {

            // Escape encode our any html
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/\//g, "&#x2F;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Advanced options, add row to the table
            $('#add-row-button').on('click', function() {
                const label = escapeHtml($('#label-input').val());
                const value = escapeHtml($('#value-input').val());
                const refFiles = escapeHtml($('#ref-files-input').val());
                $('#advanced-options-table tbody').append(
                    '<tr class="units-table-row"><td class="units-table-cell">' + label + 
                    '</td><td class="units-table-cell">' + value + '</td><td class="units-table-cell">' + 
                    refFiles + '</td><td class="units-table-cell adv-trash"><span tabindex="100" class="delete-row-button"><i class="fas fa-trash"></i> Delete</span></td></tr>'
                );
                $('#label-input').val('');
                $('#value-input').val('');
                $('#ref-files-input').val('');
                updateAdvOptions();
            });

            // Delete row from the table
            $(document).on('click', '.delete-row-button', function() {
                $(this).closest('tr').remove();
                updateAdvOptions();
            });
            $('.delete-row-button').on('keydown', function(e) {
                if (e.keyCode == 13 || e.keyCode == 32) {
                    $(this)[0].click();
                }
            });

            // Expand/collapse advanced options
            $('#advanced-opt').hide();
            $('#advanced-opt').prev().on('click', function() {
                $('#advanced-opt').slideToggle();
                $(this).find('i').toggleClass('fa-caret-right fa-caret-down');
            });

            // Checkbox click, update continue button options
            $('.export_option').on('click', function() {
                updateOptions();
            });

            function updateOptions() {
                let options = [];
                $('.export_option').each(function() {
                    if ( $(this).is(':checked') ) {
                        options.push($(this).attr('id'));
                    }
                });
                options = options.join(',');
                $('#export_options').val(options);
            }

            function updateAdvOptions() {
                let tableData = [];
                $('#advanced-options-table tbody tr').each(function() {
                    let row = $(this).find('td').map(function() {
                        return $(this).text();
                    }).get();
                    row.pop();

                    // Label the columns, make ref_files an array
                    row = {
                        'label': row[0],
                        'value': row[1],
                        'ref_files': row[2].split('\n'),
                    };
                    tableData.push(row);
                });
                $('#export_adv_options').val(JSON.stringify(tableData));
            }

            updateOptions();
            updateAdvOptions();
        });
    })(jQuery);
</script>