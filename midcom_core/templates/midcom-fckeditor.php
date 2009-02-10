<?php
/**
 * FCKeditor connector template
 *
 * @todo move to midcom_helper_datamanager
 * @package midcom_core
 */
echo "<?xml version=\"1.0\"?>\n";
?>
<Connector command="GetFolders" resourceType="File" tal:attributes="resourceType midcom_core/type; command midcom_core/command">
    <CurrentFolder path="/Samples/Docs/" url="/UserFiles/File/Samples/Docs/" tal:attributes="path midcom_core/current_folder; url midcom_core/current_folder;" />
    <Folders tal:repeat="folder midcom_core/folders">
        <Folder name="Documents" tal:attributes="name folder/name" />
    </Folders>
    <Files tal:repeat="file midcom_core/files" tal:condition="exists:midcom_core/files">
      <File name="XML Definition.doc" size="14" tal:attributes="name file/name; size file/size" />
    </Files>
</Connector>