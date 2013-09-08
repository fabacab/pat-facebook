<?php
/**
 * This file provides assorted functions for "templating" output.
 */

// TODO: This could eventually be a little less messy. Perhaps doing something
//       like rolling it into a PATHtmlTemplate object would be nice.

/**
 * Outputs an HTML5 data list of a user's friends for searching reportees.
 *
 * @param array $params Array of named parameters. For instance:
 *
 *                      array(
 *                          'label' => 'Label text',
 *                          'description_html' => '<em>raw</em> HTML will not be escaped',
 *                          'description' => 'String that will be htmlentities()'d escaped.'
 *                      )
 */
function reporteeNameField ($params = array()) {
    global $me, $reportee_id, $reportee_data;
?>
            <input type="hidden" id="reportee_id" name="reportee_id" value="<?php print he($reportee_id);?>" />
            <label>
                <?php print he($params['label']);?>
                <img id="reportee_picture" alt=""
                    <?php if ($reportee_id) : ?>
                    src="https://graph.facebook.com/<?php print he($reportee_id);?>/picture"
                    <?php else : ?>
                    style="display: none;"
                    <?php endif;?>
                />
                <input list="friends-list" id="reportee_name" name="reportee_name" value="<?php print he($reportee_data['name']);?>"
                    placeholder="Joe Shmo" required="required"
                    <?php if ($reportee_data['name']) { print 'size="' . (strlen($reportee_data['name'])) . '"'; } ?>
                />.
                <datalist id="friends-list">
                    <select><!-- For non-HTML5 fallback. -->
                        <?php foreach ($me->getFriends() as $friend) : ?>
                        <option value="<?php print he($friend['name']);?>"><?php print he($friend['id']);?></option>
                        <?php endforeach;?>
                    </select>
                </datalist>
                <span class="description"><?php print ($params['description_html']) ? $params['description_html'] : he($params['description']);?></span>
            </label>

<?php
}

function clarifyReportee ($search_results, $params = array()) {
    global $app_info;
?>
        <p><strong>Which "<?php print he($_REQUEST['reportee_name']);?>" did you mean?</strong></p>
        <p class="description"><?php print he($params['description']);?></p>
        <?php if ($search_results) { ?>
        <ul id="disambiguate-reportee">
            <?php foreach ($search_results as $result) : ?>
            <li><label><input type="radio" name="reportee_id" value="<?php print he($result['id']);?>" /> <img alt="" src="https://graph.facebook.com/<?php print he($result['id']);?>/picture" /><a href="<?php print he($result['link']);?>" target="_blank"><?php print he($result['name']);?> (<?php print ($result['gender']) ? he($result['gender']): he('unknown');?>)</a></label></li>
            <?php endforeach;?>
        </ul>
        <input type="submit" name="submit_clarification" value="Yes, that's who I mean." />
        <? } else { ?>
        <p>Sorry, but <?php print he(idx($app_info, 'name'));?> couldn't find anyone matching that description.</p>
        <input type="submit" name="no_match_found" value="Go back to search again" />
        <?php } ?><?php
}

function reportList ($reports) {
?>
    <p class="pat-reports description"><strong>Legend:</strong> Reports you've recently viewed look like <a href="/" rel="bookmark">this</a>. Reports you haven't yet viewed look like <a href="http://not-actually-a-real-place-to-visit.example/" rel="bookmark">this</a>.</p>
    <ol class="pat-reports">
        <?php foreach ($reports as $v) : ?>
        <li><a rel="bookmark" href="<?php print he("{$_SERVER['PHP_SELF']}?action=lookup&id={$v->id}");?>">View report filed on <?php print he(date('F j, Y', strtotime($v->report_date)));?>: <?php print he($v->report_title);?></a>.</li>
        <?php endforeach;?>
    </ol>
<?php
}
