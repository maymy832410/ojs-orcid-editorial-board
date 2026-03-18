{**
 * templates/editorialBoardTab.tpl
 *
 * Adds the Editorial Board (ORCID) management grid into the Website settings UI.
 *}
<tab id="editorialBoardOrcid" label="{translate key="plugins.generic.orcidEditorialBoard.tabTitle"}">
	{capture assign=editorialBoardGridUrl}{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT component="plugins.generic.orcidEditorialBoard.controllers.grid.EditorialBoardGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="editorialBoardGridContainer" url=$editorialBoardGridUrl}
</tab>
