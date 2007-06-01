<?php
// Available request keys: controller, schema, schemadb
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['l10n']->get('report flight'); ?></h1>
<div class="fi_mik_lentopaikkakisa_report">
    <form name="lentopaikkakisa_form" method="post">
        <label>
            <span>Lentäjä:</span>
            <?php
            $person = $_MIDCOM->auth->user->get_storage();
            echo $person->name;
            ?>
            <span id="smallprint" class="info">
                <a href="&(_MIDGARD['self']);midcom-logout-">[kirjaudu ulos jos et ole &(person.firstname);]</a>
            </span>
        </label>
             
        <label class="line_top">
            <span>Lennon päiväys:</span>
            <input name="date" value="<?php echo date('Y-m-d'); ?>" />
        </label>

        <label><span>Lentopaikat:</span>
            <table>
                <thead>
                    <tr>
                        <th>Mistä</th>
                        <th>Pisteet</th>
                        <th>Mihin</th>
                        <th>Pisteet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($i < 5)
                    {
                        $i++;
                        ?>
                        <tr>
                            <td><input name="origin[&(i);]" size="4" maxlenght="4" /></td>
                            <td>
                                <select name="score_origin[&(i);]">
                                    <optgroup label="Valitse pistemäärä">
                                        <option value="1">1 piste (Suomessa)</option>
                                        <option value="1.5">1.5 pistettä (ulkomailla)</option>
                                        <option value="2">2 pistettä (Suomessa, yli 800km)</option>
                                        <option value="3">3 pistettä (ulkomailla, yli 800km)</option>
                                    </optgroup>
                                </select>
                            </td>
                            <td><input name="destination[&(i);]" size="4" maxlenght="4" /></td>
                            <td>
                                <select name="score_destination[&(i);]">
                                    <optgroup label="Valitse pistemäärä">
                                        <option value="1">1 piste (Suomessa)</option>
                                        <option value="1.5">1.5 pistettä (ulkomailla)</option>
                                        <option value="2">2 pistettä (Suomessa, yli 800km)</option>
                                        <option value="3">3 pistettä (ulkomailla, yli 800km)</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                       <td colspan="4">
                          <div class="helptext"><b>Huom!</b> Lentopaikat ICAO-koodeina!
                             Joka lennosta syötetään lähtö- ja laskupaikka sekä kyseisten
                             kenttien pistemäärät niiden sijainnin perusteella.</div>
                       </td>
                    </tr>
                </tbody>
                <!-- /rullaati -->
            </table>
        </label>
      
        <label>
            <span>Kerho:</span>
            <select name="operator">
                <?php
                $qb = org_openpsa_contacts_group::new_query_builder();
                $qb->add_order('official');
                $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
                $qb->add_constraint('official', '<>', '');
                $organizations = $qb->execute();
                foreach ($organizations as $organization)
                {
                    echo "<option value=\"{$organization->id}\">{$organization->official}</option>\n";
                }
                ?>
            </select>
        </label>

        <label>
            <span>Lentokone:</span>
            <select name="aircraft">
                <?php
                $qb = org_openpsa_calendar_resource_dba::new_query_builder();
                $qb->add_order('title');
                $resources = $qb->execute();
                foreach ($resources as $resource)
                {
                    echo "<option value=\"{$resource->id}\">{$resource->title}</option>\n";
                }
                ?>
            </select>
        </label>
        <label class="line_top">
            <span>&nbsp;</span>
            <input type="submit" name="save" accesskey="s" value="Lähetä ilmoitus" />
        </label>
             
    </form>
</div>
