<?
   if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
      die();

   if (!defined("WIZARD_TEMPLATE_ID"))
      return;

   $templateID = $wizard->GetVar("wizTemplateID");
   // $sliderFile = ($templateID == "dresscode") ? "slider.xml" : "sliderV2.xml";
   // $sliderID = ($templateID == "dresscode") ? "3" : "35";

   $bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$templateID;

   CopyDirFiles(
      $_SERVER["DOCUMENT_ROOT"].WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH."/site")."/".$templateID,
      $bitrixTemplateDir,
      $rewrite = true,
      $recursive = true, 
      $delete_after_copy = false,
      $exclude = ""
   );

   $obSite = new CSite();
   $obSite->Update(WIZARD_SITE_ID, array(
      'ACTIVE' => "Y",
      'TEMPLATE'=>array(
         array(
            "CONDITION" => "",
            "SORT" => 1,
            "TEMPLATE" => $templateID
         )
      )
   ));

   $wizrdTemplateId = $templateID;
   COption::SetOptionString("main", "wizard_template_id", $wizrdTemplateId, false, WIZARD_SITE_ID);

?>
