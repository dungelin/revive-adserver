<?php // $Revision$

/************************************************************************/
/* phpAdsNew 2                                                          */
/* ===========                                                          */
/*                                                                      */
/* Copyright (c) 2001 by the phpAdsNew developers                       */
/* http://sourceforge.net/projects/phpadsnew                            */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");


// Security check
phpAds_checkAccess(phpAds_Admin+phpAds_Affiliate);



/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Affiliate))
{
	$result = phpAds_dbQuery("
		SELECT
			affiliateid
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			zoneid = $zoneid
		") or phpAds_sqlDie();
	$row = phpAds_dbFetchArray($result);
	
	if ($row["affiliateid"] == '' || phpAds_getUserID() != $row["affiliateid"] || !phpAds_isAllowed(phpAds_LinkBanners))
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
	else
	{
		$affiliateid = $row["affiliateid"];
	}
}



/*********************************************************/
/* Process submitted form                                */
/*********************************************************/

if (isset($submit))
{
	// Edit
	if (isset($zoneid) && $zoneid != '')
	{
		if (isset($description)) $description = addslashes ($description);
		
		if ($zonetype == phpAds_ZoneBanners)
		{
			if (isset($bannerid) && is_array($bannerid))
			{
				for ($i=0;$i<sizeof($bannerid);$i++)
					$bannerid[$i] = 'bannerid:'.$bannerid[$i];
				
				$what = implode (',', $bannerid);
			}
		}
		
		if ($zonetype == phpAds_ZoneCampaign)
		{
			if (isset($clientid) && is_array($clientid))
			{
				for ($i=0;$i<sizeof($clientid);$i++)
					$clientid[$i] = 'clientid:'.$clientid[$i];
				
				$what = implode (',', $clientid);
			}
		}
		
		$res = phpAds_dbQuery("
			UPDATE
				".$phpAds_config['tbl_zones']."
			SET
				what = '$what',
				zonetype = $zonetype
			WHERE
				zoneid=$zoneid
			") or phpAds_sqlDie();
		
		// Rebuild Cache
		phpAds_RebuildZoneCache ($zoneid);
		
		header ("Location: zone-index.php?affiliateid=".$affiliateid);
		exit;
	}
}


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

$extra = '';

$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_zones']."
	WHERE
		affiliateid = ".$affiliateid."
	") or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
{
	if ($zoneid == $row['zoneid'])
		$extra .= "&nbsp;&nbsp;&nbsp;<img src='images/box-1.gif'>&nbsp;";
	else
		$extra .= "&nbsp;&nbsp;&nbsp;<img src='images/box-0.gif'>&nbsp;";
	
	$extra .= "<a href='zone-include.php?affiliateid=".$affiliateid."&zoneid=".$row['zoneid']."'>".phpAds_buildZoneName ($row['zoneid'], $row['zonename'])."</a>";
	$extra .= "<br>"; 
}

$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";


if (phpAds_isUser(phpAds_Admin))
{
	$extra .= "<form action='zone-modify.php'>";
	$extra .= "<input type='hidden' name='zoneid' value='$zoneid'>";
	$extra .= "<input type='hidden' name='returnurl' value='zone-include.php'>";
	$extra .= "<br><br>";
	$extra .= "<b>$strModifyZone</b><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-move-zone.gif' align='absmiddle'>&nbsp;$strMoveTo<br>";
	$extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
	$extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$extra .= "<select name='moveto' style='width: 110;'>";
	
	$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_affiliates']." WHERE affiliateid != ".$affiliateid) or phpAds_sqlDie();
	while ($row = phpAds_dbFetchArray($res))
		$extra .= "<option value='".$row['affiliateid']."'>".phpAds_buildAffiliateName($row['affiliateid'], $row['name'])."</option>";
	
	$extra .= "</select>&nbsp;<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='zone-delete.php?affiliateid=$affiliateid&zoneid=$zoneid&returnurl=zone-index.php'".phpAds_DelConfirm($strConfirmDeleteZone).">$strDelete</a><br>";
	$extra .= "</form>";
	
	
	$extra .= "<br><br><br>";
	$extra .= "<b>$strShortcuts</b><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<a href=affiliate-edit.php?affiliateid=$affiliateid>$strAffiliateProperties</a><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	$extra .= "<img src='images/icon-statistics.gif' align='absmiddle'>&nbsp;<a href=stats-affiliate-zones.php?affiliateid=$affiliateid>$strStats</a><br>";
	$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
	
	phpAds_PageHeader("4.2.3.3", $extra);
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections(array("4.2.3.2", "4.2.3.3", "4.2.3.4", "4.2.3.5"));
}
else
{
	if (phpAds_isAllowed(phpAds_EditZone)) $sections[] = "2.1.2";
	$sections[] = "2.1.3";
	$sections[] = "2.1.4";
	$sections[] = "2.1.5";
	
	phpAds_PageHeader("2.1.3", $extra);
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;".phpAds_getAffiliateName($affiliateid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;<b>".phpAds_getZoneName($zoneid)."</b><br><br><br>";
		phpAds_ShowSections($sections);
}




/*********************************************************/
/* Main code                                             */
/*********************************************************/

function phpAds_showZoneCampaign ($width, $height, $what)
{
	global $phpAds_config;
	global $strName, $strID, $strDescription;
	global $strEdit, $strCheckAllNone;
	global $strNoCampaignsToLink, $strMatchingBanners;
	
	
	$what_array = explode(",",$what);
	for ($k=0; $k < count($what_array); $k++)
	{
		if (substr($what_array[$k],0,9)=="clientid:")
		{
			$clientid = substr($what_array[$k],9);
			$clientids[$clientid] = true;
		}
	}
	
	// Fetch all campaigns
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_clients']."
		WHERE
			parent > 0
	") or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		$campaigns[$row['clientid']] = $row;
	}
	
	
	// Fetch all banners which can be linked
	$query = "
		SELECT
			bannerid,
			clientid,
			alt,
			description,
			active,
			storagetype
		FROM
			".$phpAds_config['tbl_banners']."
		";
	
	if ($width != -1 && $height != -1)
		$query .= "WHERE width = $width AND height = $height";
	elseif ($width != -1)
		$query .= "WHERE width = $width";
	elseif ($height != -1)
		$query .= "WHERE height = $height";
	
	$query .= "
		ORDER BY
			bannerid";
	
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		$campaigns[$row['clientid']]['banners'][$row['bannerid']] = $row;
	}
	
	
	
	
	
	// Header
	echo "<table width='100%' border='0' align='center' cellspacing='0' cellpadding='0'>";
	echo "<tr height='25'>";
	echo "<td height='25'><b>&nbsp;&nbsp;$strName</b></td>";
	echo "<td height='25'><b>$strID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>";
	echo "<td height='25'><b>$strDescription</b></td>";
	echo "</tr>";
	
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	$i = 0;
	$checkedall = true;
	
	if (phpAds_dbNumRows($res) == 0)
	{
		echo "<tr bgcolor='#F6F6F6'><td colspan='3' height='25'>&nbsp;&nbsp;".$strNoCampaignsToLink."</td></tr>";
	}
	else
	{
		for (reset($campaigns); $ckey = key($campaigns); next($campaigns))
		{
			$campaign = $campaigns[$ckey];
			
			if (isset($campaign['banners']) && is_array($campaign['banners']) && count($campaign['banners']))
			{
				$name = phpAds_breakString ($name, '60');
				
				if ($i > 0) echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
				echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
				
				// Begin row
				echo "<td height='25'>";
				echo "&nbsp;&nbsp;";
				
				// Show checkbox
				if (isset($clientids[$campaign['clientid']]) && $clientids[$campaign['clientid']] == true)
					echo "<input type='checkbox' name='clientid[]' value='".$campaign['clientid']."' checked onclick='reviewall();'>"; 
				else
				{
					echo "<input type='checkbox' name='clientid[]' value='".$campaign['clientid']."' onclick='reviewall();'>"; 
					$checkedall = false;
				}
				
				// Space
				echo "&nbsp;&nbsp;";
				
				// Banner icon
				if ($campaign['active'] == 't')
					echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;";
				else
					echo "<img src='images/icon-campaign-d.gif' align='absmiddle'>&nbsp;";
				
				// Name
				echo "<a href='campaign-edit.php?campaignid=".$campaign['clientid']."'>";
				echo phpAds_breakString ($campaign['clientname'], '60')."</a>";
				echo "</td>";
				
				// ID
				echo "<td height='25'>".$campaign['clientid']."</td>";
				
				// Edit
				echo "<td height='25'>";
				echo str_replace ('{count}', count($campaign['banners']), $strMatchingBanners);
				echo "</td>";
				
				// End row
				echo "</tr>";
				$i++;
			}
		}
	}
	
	// Footer
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	echo "<tr><td height='25'>";
	echo "&nbsp;&nbsp;<input type='checkbox' name='checkall' value=''".($checkedall == true ? ' checked' : '')." onclick='toggleall();'>";
	echo "&nbsp;&nbsp;".$strCheckAllNone;
	echo "</td></tr>";
	
	echo "</table>";
}



function phpAds_showZoneBanners ($width, $height, $what)
{
	global $phpAds_config;
	global $strName, $strID, $strUntitled, $strDescription;
	global $strEdit, $strCheckAllNone;
	global $strNoBannersToLink;
	
	
	$what_array = explode(",",$what);
	for ($k=0; $k < count($what_array); $k++)
	{
		if (substr($what_array[$k],0,9)=="bannerid:")
		{
			$bannerid = substr($what_array[$k],9);
			$bannerids[$bannerid] = true;
		}
	}
	
	// Fetch all campaigns
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_clients']."
		WHERE
			parent > 0
	") or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		$campaigns[$row['clientid']] = $row;
	}
	
	
	// Fetch all banners which can be linked
	$query = "
		SELECT
			bannerid,
			clientid,
			alt,
			description,
			active,
			storagetype
		FROM
			".$phpAds_config['tbl_banners']."
		";
	
	if ($width != -1 && $height != -1)
		$query .= "WHERE width = $width AND height = $height";
	elseif ($width != -1)
		$query .= "WHERE width = $width";
	elseif ($height != -1)
		$query .= "WHERE height = $height";
	
	$query .= "
		ORDER BY
			bannerid";
	
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		$campaigns[$row['clientid']]['banners'][$row['bannerid']] = $row;
	}
	
	
	
	
	
	// Header
	echo "<table width='100%' border='0' align='center' cellspacing='0' cellpadding='0'>";
	echo "<tr height='25'>";
	echo "<td height='25'><b>&nbsp;&nbsp;$strName</b></td>";
	echo "<td height='25'><b>$strID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>";
	echo "<td height='25'><b>$strDescription</b></td>";
	echo "</tr>";
	
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	$i = 0;
	$checkedall = true;
	
	if (phpAds_dbNumRows($res) == 0)
	{
		echo "<tr bgcolor='#F6F6F6'><td colspan='3' height='25'>&nbsp;&nbsp;".$strNoBannersToLink."</td></tr>";
	}
	else
	{
		for (reset($campaigns); $ckey = key($campaigns); next($campaigns))
		{
			$campaign = $campaigns[$ckey];
			
			if (isset($campaign['banners']) && is_array($campaign['banners']) && count($campaign['banners']))
			{
				$banners = $campaign['banners'];
				
				for (reset($banners); $bkey = key($banners); next($banners))
				{
					$banner = $banners[$bkey];
					
					$name = $strUntitled;
					if (isset($banner['alt']) && $banner['alt'] != '') $name = $banner['alt'];
					if (isset($banner['description']) && $banner['description'] != '') $name = $banner['description'];
					
					$name = phpAds_breakString ($name, '60');
					
					if ($i > 0) echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
					
				    echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
					
					// Begin row
					echo "<td height='25'>";
					echo "&nbsp;&nbsp;";
					
					// Show checkbox
					if (isset($bannerids[$banner['bannerid']]) && $bannerids[$banner['bannerid']] == true)
						echo "<input type='checkbox' name='bannerid[]' value='".$banner['bannerid']."' checked onclick='reviewall();'>"; 
					else
					{
						echo "<input type='checkbox' name='bannerid[]' value='".$banner['bannerid']."' onclick='reviewall();'>"; 
						$checkedall = false;
					}
					
					// Space
					echo "&nbsp;&nbsp;";
					
					// Banner icon
					if ($campaign['active'] == 't' && $banner['active'] == 't')
					{
						if ($banner['storagetype'] == 'html')
							echo "<img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;";
						elseif ($banner['storagetype'] == 'url')
							echo "<img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;";
						else
							echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
					}
					else
					{
						if ($banner['storagetype'] == 'html')
							echo "<img src='images/icon-banner-html-d.gif' align='absmiddle'>&nbsp;";
						elseif ($banner['storagetype'] == 'url')
							echo "<img src='images/icon-banner-url-d.gif' align='absmiddle'>&nbsp;";
						else
							echo "<img src='images/icon-banner-stored-d.gif' align='absmiddle'>&nbsp;";
					}
					
					// Name
					echo "<a href='banner-edit.php?campaignid=".$campaign['clientid']."&bannerid=".$banner['bannerid']."'>";
					echo $name;
					echo "</a></td>";
					
					// ID
					echo "<td height='25'>".$banner['bannerid']."</td>";
					
					// Edit
					echo "<td height='25'>".$banner['description']."</td>";
					
					// End row
					echo "</tr>";
					$i++;
				}
			}
		}
	}
	
	// Footer
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	echo "<tr><td height='25'>";
	echo "&nbsp;&nbsp;<input type='checkbox' name='checkall' value=''".($checkedall == true ? ' checked' : '')." onclick='toggleall();'>";
	echo "&nbsp;&nbsp;".$strCheckAllNone;
	echo "</td></tr>";
	
	echo "</table>";
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

?>

<script language='Javascript'>
<!--
	function toggleall()
	{
		allchecked = false;
		
		for (var i=0; i<document.zonetypeselection.elements.length; i++)
		{
			if (document.zonetypeselection.elements[i].name == 'bannerid[]' ||
				document.zonetypeselection.elements[i].name == 'clientid[]')
			{
				if (document.zonetypeselection.elements[i].checked == false)
				{
					allchecked = true;
				}
			}
		}
		
		for (var i=0; i<document.zonetypeselection.elements.length; i++)
		{
			if (document.zonetypeselection.elements[i].name == 'bannerid[]' ||
				document.zonetypeselection.elements[i].name == 'clientid[]')
			{
				document.zonetypeselection.elements[i].checked = allchecked;
			}
		}
	}
	
	function reviewall()
	{
		allchecked = true;
		
		for (var i=0; i<document.zonetypeselection.elements.length; i++)
		{
			if (document.zonetypeselection.elements[i].name == 'bannerid[]' ||
				document.zonetypeselection.elements[i].name == 'clientid[]')
			{
				if (document.zonetypeselection.elements[i].checked == false)
				{
					allchecked = false;
				}
			}
		}
		
				
		document.zonetypeselection.checkall.checked = allchecked;
	}	
//-->
</script>

<?php

if (isset($zoneid) && $zoneid != '')
{
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_zones']."
		WHERE
			zoneid = $zoneid
		") or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res))
	{
		$zone = phpAds_dbFetchArray($res);
	}
}

// Set the default zonetype
if (!isset($zonetype) || $zonetype == '')
	$zonetype = $zone['zonetype'];




echo "<form name='zonetypes' method='post' action='zone-include.php'>";
echo "<input type='hidden' name='zoneid' value='".$zoneid."'>";
echo "<input type='hidden' name='affiliateid' value='".$affiliateid."'>";

echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25' colspan='3'><b>".$strSelectZoneType."</b></td></tr>";
echo "<tr><td height='25'>";

echo "<select name='zonetype' onChange='this.form.submit();'>";
	echo "<option value='".phpAds_ZoneCampaign."'".(($zonetype == phpAds_ZoneCampaign) ? " selected" : "").">".$strCampaignSelection."</option>";
	echo "<option value='".phpAds_ZoneBanners."'".(($zonetype == phpAds_ZoneBanners) ? " selected" : "").">".$strBannerSelection."</option>";
	//echo "<option value='".phpAds_ZoneInteractive."'".(($zonetype == phpAds_ZoneInteractive) ? " selected" : "").">".$strInteractive."</option>";
	echo "<option value='".phpAds_ZoneRaw."'".(($zonetype == phpAds_ZoneRaw) ? " selected" : "").">".$strRawQueryString."</option>";
echo "</select>";
echo "&nbsp;<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif' border='0'>";

echo "</td></tr>";
echo "</table>";
phpAds_ShowBreak();
echo "</form>";
echo "<br>";



echo "<form name='zonetypeselection' method='post' action='zone-include.php'>";
echo "<input type='hidden' name='zoneid' value='".$zoneid."'>";
echo "<input type='hidden' name='affiliateid' value='".$affiliateid."'>";
echo "<input type='hidden' name='zonetype' value='$zonetype'>";


if ($zonetype == phpAds_ZoneCampaign)
{
	phpAds_showZoneCampaign($zone["width"], $zone["height"], $zone["what"]);
}

if ($zonetype == phpAds_ZoneBanners)
{
	phpAds_showZoneBanners($zone["width"], $zone["height"], $zone["what"]);
}

if ($zonetype == phpAds_ZoneRaw)
{
	echo "<textarea cols='50' rows='16' name='what' style='width:600px;'>".(isset($zone['what']) ? $zone['what'] : '')."</textarea>";
}


echo "<br><br>";
echo "<br><br>";

echo "<input type='submit' name='submit' value='$strSaveChanges'>";
echo "</form>";



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>
