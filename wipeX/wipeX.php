<?php
#####################################################
#	A well featured PEX web interface.
#
#		Author: NINJ4
#		Version: Beta 0.5
#		Created 12/20
#		Last Edited: 
#####################################################
#	Page:

require( "wipeX_config.php" );

$groups_ary = loadGroups( $mcConn );

wipeX_header();
?>
<!-- WIPEX by NINJ4. For more information, visit SoftSlayer.com -->
<link rel="stylesheet" type="text/css" href="wipex.css" />
<div class="wipex-panel">
	<script type="text/javascript" src="lib/jquery.js"></script>
	<script type="text/javascript" src="wipex.js"></script>
	<div class="finishedOps">
		<div class="inner"><span class="corners-top"><span></span></span>
			<p class="finishedOps" id="OpsTitle"><h2 id="OpsTitle">Operations Pending:</h2></p>
			<p class="finishedOps" id="OpsContent"></p>
			<hr>
			<div align="right"><span id="submitLoading"></span><a href="javascript:void(0)" class="action ClearPending">Clear Pending Operations</a>&nbsp;<a href="javascript:void(0)" class="action Submit">Apply Changes</a></div>
		<span class="corners-bottom"><span></span></span></div>
	</div>
	<div class="wipex-topbar" align="center">
	<h2>Web-Interface for PEX</h2>
	<table><tr><td><div align="left"><span style="font-size:75%;color:white;">wipeX Beta Version 0.5 by NINJ4 @ <a href="softslayer.com">SoftSlayer.com</a></span></div></td><td><div align="right"><a href="javascript:void(0)" id="showLogs" class="action loadLogs">Show wipeX Logs</a></div></td></tr></table>
	<div class="wipex-container hidden" id="Show">
		<form id="">
			<table class="wipex-logs" cellspacing="0px">
				<thead class="logHead">
					<tr>
						<th>Timestamp</th>
						<th>User</th>
						<th>Action</th>
						<th></th>
					</tr>
				</thead>
				<tbody class="logs-rows">
				</tbody>
			</table>
			<b><div class="logLoader">
					Click to load more logs...
			</div></b>
			<div align="right">With Selected:
				<select id="logAction">
					<option value="-1">------------------</option>
					<option value="undo">Undo Actions</option>
				</select>
			</div>
		</form>
	</div>
	<div class="wipex-container" id="Hide">
		<table class="wipex">
			<thead>
				<tr>
					<th width="30%">Groups</th>
					<th width="30%">Permissions</th>
					<th width="30%">Members</th>
				</tr>
			</thead>
			<tr class="selectors">
				<td>
					<select id="column1" name="GroupsList" multiple="multiple">
						<?php
								foreach ( $groups_ary as $group_ary ) {
									echo "<option class='group' value='". $group_ary['name'] ."'"
									. ( ( $group_ary['default'] ) ? " style='background-color:yellow;'>Default Group: " : ">" )
									. $group_ary['name'] ."</option>\n";
								}
						?>
					</select>
				</td>
				<td>
					<select id="column2" name="DataList" multiple="multiple"></select>
				</td>
				<td>
					<select id="column3" name="DataList" multiple="multiple"></select>
				</td>
			</tr>
			<tr>
				<td class="wipex-info" id="col1Config">
					<div align="right" class="tdlink"><a href="javascript:void(0)" class="expand col1">expand view</a></div>
					<div class="grpConfig slidePanel">Select the function to apply to the following groups:
						<b><div id="selectedGroups">
						</div></b>
						<a href="javascript:void(0)" id="remGrp" class="action Confirm col1">Remove Groups from Database</a>
					</div>
					<a href="javascript:void(0)" id="newGrp" class="action Ask col1">Create New Group</a>
				</td>
				<td class="wipex-info" id="col2Config">
					<div align="right" class="tdlink"><a href="javascript:void(0)" class="expand col2 hidden">expand view</a></div>
					<div class="permsConfig slidePanel">Select the function to apply to the following nodes:
						<b><div id="selectedPerms">
						</div></b>
						<a href="javascript:void(0)" id="remPerm" class="action Do col2">Remove Permissions from Group</a>
					</div>
					<div class="rgAddItems" id="rgPermAdd">
						<p><a href="javascript:void(0)" id="addPerm" class="action Ask col2">Add Permissions to Group</a></p>
						<p><a href="javascript:void(0)" id="addParent" class="action Ask col2">Add Parents to Group</a></p>
						<p><a href="javascript:void(0)" id="setRank" class="action Pop col2">Set Group Rank</a></p>
						<p><a href="javascript:void(0)" id="setLadder" class="action Pop col2">Set Group Rank-Ladder</a></p>
					</div>
				</td>
				<td class="wipex-info" id="col3Config">
					<div align="right" class="tdlink"><a href="javascript:void(0)" class="expand col3 hidden">expand view</a></div>
					<div class="memberConfig slidePanel">Select the function to apply to the following members:
						<b><div id="selectedMembers">
						</div></b>
						<a href="javascript:void(0)" id="remMem" class="action Do col3">Remove Members from Group</a>
					</div>
					<div class="rgAddItems" id="rgMemAdd">
						<a href="javascript:void(0)" id="addMem" class="action Ask col3">Add Members to Group</a>
					</div>
				</td>
			</tr>
		</table>
	</div>
	</div>
	<div class="input-overlay">
		<div class="overlay-content">
			<p class="input-prompt">Please input the name(s) of the <span class="overlay-items"></span>s you would like to <span id="overlay-action">add to the Group</span>.<br />Please input one <span class="overlay-items"></span> per line</p>
			<div><textarea class="input-area"></textarea></div>
			<p><a href="javascript:void(0)" id="resetOverlay" class="action">Reset</a> &nbsp; <a href="javascript:void(0)" class="cancelOverlay action">Cancel</a> &nbsp; <a href="javascript:void(0)" id="" class="action AskSubmit">Submit</a></p>
		</div>
	</div>
	<div class="expand-overlay" align="center">
		<div class="expanded-content">
				<h3 id="expanded-title"></h3>
				<select id="colExpanded" multiple="multiple">
				</select>
				<p id="expandConfig"></p><br /><a href="javascript:void(0)" class="cancelOverlay action">Close Window</a>
		</div>
	</div>
</div>
<?php
wipeX_footer();
?>

