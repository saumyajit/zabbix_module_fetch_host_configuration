<?php

#require_once './include/config.inc.php';
#require_once './include/hosts.inc.php';
#require_once './include/maintenances.inc.php';
#require_once './include/forms.inc.php';
#require_once './include/users.inc.php';

require_once './include/page_header.php';
#
foreach (glob("./include/classes/html/*.php") as $filename)
{
	require_once $filename;
//	echo $filename;
}

static $map_status = array(
	0 => '<span class="green">monitored</span>',
	1 => '<span class="red">unmonitored</span>'
);
static $map_maintenance_status  = array(
        0 => 'no maintenance',
        1 => 'maintenance in effect <a class="icon-maintenance"></a>'
);
static $map_item_status  = array(
        0 => '<span class="green">Enabled</span>',
        1 => '<span class="red">Disabled</span>'
);
static $map_item_state  = array(
        0 => '<span class="green">Normal</span>',
        1 => '<span class="red">Not Supported</span>'
);
static $map_trigger_status  = array(
        0 => '<span class="green">Enabled</span>',
        1 => '<span class="red">Disabled</span>'
);
static $map_trigger_state  = array(
        0 => '<span class="green">Normal</span>',
        1 => '<span class="red">Not Supported</span>'
);
static $map_trigger_priority  = array(
        0 => 'Not classified',
        1 => 'Information',
        2 => 'Low',
        3 => 'Medium',
        4 => 'High',
        5 => 'Critical'
);

function build_table($array){
	$html="";
	foreach($array as $key => $value ){
	    $html .= '<tr>';
	    $html .= '<th>' . htmlspecialchars($key) . '</th>';
	    $html .= '<td>' . htmlspecialchars($value) . '</td>';
	    $html .= '</tr>';
        }

    return $html;
}
?>

<style>
table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  text-align: left;
  padding: 8px;
}
table td + td {
/*  border-right: solid 1px ; */
  border-left: solid 1px;
  border-style: dotted;
}
table th + td {
/*  border-right: solid 1px ; */
  border-left: solid 1px;
  border-style: dotted;
}

thead {
 border-bottom: solid 1px;
 border-style: dotted;
}

tr { border: none; }

tr:nth-child(even) {background-color: #38383840;}

summary h2 {
  display: inline-block;
}
.tooltip
{
  text-decoration:none;
  position:relative;
}


.tooltip span
{
  display:none;
  /*color:black;
  background:white;*/
}

.tooltip:hover span
{
  display:block;
  position:absolute;
  top:0;
 left:-75%;
  z-index:1000;
  width:auto;
/*  max-width:320px;
  min-height:128px;
  border:1px solid black;
  margin-top:12px;
  margin-left:32px;
  overflow:hidden;
  padding:8px;*/
}

.page-title-general {
    text-align: center;
    margin: -15px;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

</style>
<link href="./modules/get-host-ro/views/includes/css/jquery.dataTables.css" rel="stylesheet"/>
<script src="./modules/get-host-ro/views/includes/js/jquery.dataTables.js"></script>
<script>$(document).ready(function() {$('#macros').dataTable({paging: false})});</script>
<script>$(document).ready(function() {$('#inventory').dataTable({paging: false})});</script>
<script>$(document).ready(function() {$('#items').dataTable({paging: false})});</script>
<script>$(document).ready(function() {$('#triggers').dataTable({paging: false})});</script>

<header class="header-title">
	<nav class="sidebar-nav-toggle" role="navigation" aria-label="Sidebar control">
		<button type="button" id="sidebar-button-toggle" class="button-toggle" title="Show sidebar">Show sidebar</button>
	</nav>
	<div>
		<h1 id="page-title-general" >View Host Configurations</h1>
	</div>
</header>
<main>
	<form method="post">
		<div id="tabs" class="table-forms-container ui-tabs ui-widget ui-widget-content ui-corner-all" style="visibility: visible;">
			<div id="maintenanceTab" aria-labelledby="tab_maintenanceTab" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="false">
				<ul class="table-forms" id="maintenanceFormList">
					<li>
						<div class="table-forms-td-left">
							<label class="form-label-asterisk" for="host">Host </label>
						</div>
						<div class="table-forms-td-right">
							<input list="host" type="text" name="host" size="50" autocomplete="off">
							<datalist id="host">
<?php
$hosts = api::host()->get(array(
                             	'output' => array('host','name'),
                                ));
$arr_hosts=array();

foreach($hosts as $onehost){
	array_push($arr_hosts,$onehost['host']);
        if (!empty($onehost['name'])) array_push($arr_hosts,$onehost['name']);
}
//var_dump($hosts);
$arr_hosts=array_unique($arr_hosts);
sort($arr_hosts);

foreach($arr_hosts as $name){
                ?>							<option value="<?php echo $name; ?>"><?php echo $name; ?></option><?php
        }
?>
							</datalist>
							<button type="submit" value="Search"/>Search host</button>
						</div>
					</li>
				</ul>
			</div>
<?php
if (isset($_POST['host'])) {
	$host = api::host()->get(array(
                        'filter' => array('host' => $_POST['host'],'name' => $_POST['host']),
                        'output' => array('hostid'),
			'searchByAny' => 1
	));
	if (count($host) > 1  ){
		?>search with pattern "<?php echo $_REQUEST["host"]; ?>" has serverals results. Please check your search and <a href="zabbix.php?action=expandtriggermacro.view" title="Search again">retry</a>.<?php
	}
	elseif (count($host) == 0){
		?>search with pattern "<?php echo $_REQUEST["host"]; ?>" has no result. Please check your search and <a href="zabbix.php?action=gethostro.view" title="Search again">retry</a>.<?php
	} else
	{
		$hostInfo = api::host()->get(array(
			'filter' => array('hostid' => $host[0]['hostid']),
			//'searchbyany' => 1,
			'output' => array('hostid','host','name','status','description','proxy_hostid','tls_connect','tls_accept','tls_issuer','tls_subject','flags','inventory_mode','maintenance_status'),
			'selectDiscoveryRule' => array('itemid','name','parent_hostid'),
			'selectHostGroups' => array('groupid','name'),
			'selectHostDiscovery' => array('parent_hostid','host'),
			'selectInterfaces' => array('interfaceid','type','main','available','error','details','ip','dns','port','useip'),
//			'selectInventory' => array('type','type_full','name','alias','os','os_full','os_short','serialno_a','serialno_b','tag','asset_tag','macaddress_a','macaddress_b','hardware,hardware_full','software,software_full','software_app_a','software_app_b','software_app_c','software_app_d','software_app_e','contact','location','location_lat','location_lon','notes','chassis','model','hw_arch','vendor','contract_number','installer_name','deployment_status','url_a','url_b','url_c','host_networks','host_netmask','host_router','oob_ip','oob_netmask','oob_router','date_hw_purchase','date_hw_install','date_hw_expiry','date_hw_decomm','site_address_a','site_address_b','site_address_c','site_city','site_state','site_country','site_zip','site_rack','site_notes','poc_1_name','poc_1_email','poc_1_phone_a','poc_1_phone_b','poc_1_cell','poc_1_screen','poc_1_notes','poc_2_name','poc_2_email','poc_2_phone_a','poc_2_phone_b','poc_2_cell','poc_2_screen','poc_2_notes'),
			'selectInventory' => array('type','type_full','name','os','os_short','contact'),
			'selectMacros' => array('hostmacroid','macro','value','description','type','automatic'),
			'selectParentTemplates' => array('templateid','name','link_type','uuid'),
			'selectTags' => array('tag','value','automatic')
		));
		
		if (isset($hostInfo[0]['proxy_hostid'])){
			$proxyInfo= api::proxy()->get(array(
				'proxyids' => $hostInfo[0]['proxy_hostid'],
				'output' => array('host')
			));
		}

		$itemsInfo= api::item()->get(array(
			'hostids' => array($hostInfo[0]['hostid']),
			'webitems' => 1,
			'preservekeys' => 1,
			'templated' => NULL,
			'output' => array('itemid','type','name','key_','delay','history','trends','status','state','description'),
			'selectTriggers' => array('description'),
			'sortfield' => 'name'
		));
		  $triggers = API::Trigger()->get(array(
        	        'output' => array('triggerid','description','expression','priority','status','state'),
                	'filter' => array('hostid' => $host[0]['hostid']),
	                'searchByAny' => 1,
        	        'expandExpression' => true,
	                'expandDescription' => true,
	                'sortfield' =>  array('triggerid','description'),
	                'sortorder' => "ASC"
		        ));

        	$severities= API::Settings()->get(array(
                	'output' => array('severity_color_0','severity_color_1','severity_color_2','severity_color_3','severity_color_4','severity_color_5')
	        ));

?>
						<br/>
						<p>
						<div>
						<div style="padding: 1em; display: flex;">
							<div style="flex: 1;">
								<!--<details><pre><?php var_export($hostInfo[0]);?></pre></details>-->
								<h2>General information</h2>
								<table class="source-tableeditor" style="padding: 10px">
									<tbody>
										<tr>
											<th>Hostname</th>
											<td><?php echo $hostInfo[0]['host'];?></td>
										</tr>
										<tr>
											<th>Display name</th>
											<td><?php echo $hostInfo[0]['name'];?></td>
										</tr>
										<tr>
											<th>Status</th>
											<td><?php echo $map_status[$hostInfo[0]['status']];?></td>
										</tr>
										<tr>
											<th>Maintenance status</th>
											<td><?php echo $map_maintenance_status[$hostInfo[0]['maintenance_status']];?></td>
										</tr>

										<tr>
											<th>Proxy</th>
											<td><?php echo isset($proxyInfo[0]['host']) ? $proxyInfo[0]['host'] : "No proxy";?></td>
										</tr>
										<tr>
											<th>Description</th>
											<td><?php echo $hostInfo[0]['description'];?></td>
										</tr>
										<tr>
											<th>Interfaces</th>
											<td>
												<ul>
<?php 
		foreach($hostInfo[0]['interfaces'] as $interface){
												echo "<li>";
												if ($interface['ip']) { echo $interface['ip'];}
							               	        		if (($interface['ip']) && ($interface['dns'])) {echo " | ";}
							                        		if ($interface['dns']) {echo $interface['dns'];}
												if (count($hostInfo[0]['interfaces']) > 1) {echo " ";}
												echo "</li>";
					}	
?>
												</ul>
											</td>
										</tr>
										<tr>
					                                       		<th>Groups</th>
	                                					        <td>
					        	                                        <ul>
<?php
                foreach($hostInfo[0]['hostgroups'] as $hostgroup){
												echo "<li>".$hostgroup['name']."<li>";
		}
?>
	   						        	                        </ul>						
					                               	        	</td>
										</tr>
<tr>
                                                                                        <th>Templates</th>
                                                                                        <td>
                                                                                                <ul>
<?php
                foreach($hostInfo[0]['parentTemplates'] as $template){
                                                                                                echo "<li><span data-hintbox=\"1\" data-hintbox-static=\"1\" data-hintbox-contents=\"".$template['uuid']."\">".$template['name']."</span><li>";
                }
?>
                                                                                                </ul>
                                                                                        </td>
                                                                                </tr>
									</tbody>
								</table>
							</div>
							<div style="flex: 1;padding-left: 1em;">
                                                                <details open="true">
									<summary><h2>Macros</h2></summary>
									<table id="macros" style="padding: 10px" class="display list-table">
										<thead>
											<tr>
      												<th>Name</th>
												<th>Value</th>
												<th>Description</th>
											</tr>
										</thead>
                                                                                <tbody>
<?php foreach($hostInfo[0]['macros'] as $macro){ ?>
                                                                                        <tr>
                                                                                                <td><?php echo $macro['macro'];?></td>
                                                                                                <td><?php echo isset($macro['value']) ? $macro['value'] : "*** secret field ***"; ?></td>
                                                                                                <td><?php echo $macro['description'];?></td>

                                                                                       </tr>
<?php } ?>
                                                                                </tbody>
									</table>
                                                                </details>
                                                        </div>
							<div style="flex: 1; padding-left: 1em;">
								<details>
									<summary><h2>Inventory</h2></summary>
									<table id="inventory" class="source-tableeditor" style="padding: 10px">
										<thead>
											<th></th>
											<th></th>
										</thead>
										<tbody>
<?php
//		var_export($hostInfo[0]['inventory']);
	echo build_table($hostInfo[0]['inventory']);
?>
										</tbody>
									</table>
								</details>
							</div>
							</div>
							<div>
							<div style="flex: 1;padding:1em;" >
                                                                <details>
									<summary><h2>Items</h2></summary>
									<table id="items" class="source-tableeditor" style="padding: 10px">
										<thead>
											<th>Name</th>
											<th>key</th>
											<th>Interval</th>
											<th>History</th>
											<th>Trends</th>
											<th>Description</th>
											<th>Status</th>
											<th>State</th>
											<th>Triggers</th>
										</thead>
										<tbody>
<?php foreach($itemsInfo as $itemInfo){ ?>
										<tr>
											<td>
<?php
												echo "<a target='_blank' href='items.php?context=host&form=update&itemid=" .$itemInfo['itemid']."&hostid=".$hostInfo[0]['hostid']."'> ".$itemInfo['name']."</a>";
?>
											</td>
                                                                                        <td><?php echo $itemInfo['key_'];?></td>
                                                                                        <td><?php echo $itemInfo['delay'];?></td>
                                                                                        <td><?php echo $itemInfo['history'];?></td>
                                                                                        <td><?php echo $itemInfo['trends'];?></td>
                                                                                        <td><?php echo $itemInfo['description'];?></td>
                                                                                        <td><?php echo $map_item_status[$itemInfo['status']];?></td>
											<td><?php echo $map_item_state[$itemInfo['state']];?></td>
											<td>
												<a title="<?php foreach($itemInfo['triggers'] as $trigger){ echo $trigger['description']."\r\n";} ?>">
												<?php echo count($itemInfo['triggers']);?>
												</a>
											</td>
										</tr>
<?php } ?>
                                                                                </tbody>
									</table>
                                                                </details>
                                                        </div>
							<div style="flex: 1;padding: 1em;">
                                                                <details>
                                                                        <summary><h2>Triggers</h2></summary>
									<table id="triggers" class="source-tableeditor" style="padding: 10px">
										<thead>
											<tr>
									                        <th>Trigger name</th>
									                        <th>Severity</th>
									                        <th>Trigger with expand expression</th>
															<th>Status</th>
															<th>State</th>
                									</tr>
										</thead>
										<tbody>
                <?php

                foreach($triggers as $trigger){
                        echo "<tr>";
                        echo "<td><a target='_blank' href='triggers.php?context=host&form=update&triggerid=" .$trigger['triggerid']. "'> ".$trigger['description'] ."</a></td>";
                        echo "<td style='background-color: #".$severities["severity_color_$trigger[priority]"]."80'>". $map_trigger_priority[$trigger['priority']]."</td>";
                        echo "<td style='background-color: #".$severities["severity_color_$trigger[priority]"]."80'>". $trigger['expression']."</td>";
						echo "<td>".$map_trigger_status[$itemInfo['status']]."</td>";
						echo "<td>".$map_trigger_state[$itemInfo['state']]."</td>";
                        echo "</tr>";
                }
?>
        	                                              	                </tbody>
                                                                        </table>
                                                                </details>
							</div>
							</div>
							
						</div>
<?php
        }
}
?>
		</div>
	</form>
</main>
<?php

require_once './include/page_footer.php';
