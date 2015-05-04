<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "imagesinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$images_edit = NULL; // Initialize page object first

class cimages_edit extends cimages {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{4488919F-A46E-4C9B-829B-4AB14E218D15}";

	// Table name
	var $TableName = 'images';

	// Page object name
	var $PageObjName = 'images_edit';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-error ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<table class=\"ewStdTable\"><tr><td><div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div></td></tr></table>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (images)
		if (!isset($GLOBALS["images"]) || get_class($GLOBALS["images"]) == "cimages") {
			$GLOBALS["images"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["images"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'images', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up current action
		$this->id->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $DbMasterFilter;
	var $DbDetailFilter;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Load key from QueryString
		if (@$_GET["id"] <> "") {
			$this->id->setQueryStringValue($_GET["id"]);
		}

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Check if valid key
		if ($this->id->CurrentValue == "")
			$this->Page_Terminate("imageslist.php"); // Invalid key, return to list

		// Validate form if post back
		if (@$_POST["a_edit"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = ""; // Form error, reset action
				$this->setFailureMessage($gsFormError);
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues();
			}
		}
		switch ($this->CurrentAction) {
			case "I": // Get a record to display
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("imageslist.php"); // No matching record, return to list
				}
				break;
			Case "U": // Update
				$this->SendEmail = TRUE; // Send email on update success
				if ($this->EditRow()) { // Update record based on key
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("UpdateSuccess")); // Update success
					$sReturnUrl = $this->getReturnUrl();
					$this->Page_Terminate($sReturnUrl); // Return to caller
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Restore form values if update failed
				}
		}

		// Render the record
		$this->RowType = EW_ROWTYPE_EDIT; // Render as Edit
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Set up starting record parameters
	function SetUpStartRec() {
		if ($this->DisplayRecs == 0)
			return;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET[EW_TABLE_START_REC] <> "") { // Check for "start" parameter
				$this->StartRec = $_GET[EW_TABLE_START_REC];
				$this->setStartRecordNumber($this->StartRec);
			} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
				$PageNo = $_GET[EW_TABLE_PAGE_NO];
				if (is_numeric($PageNo)) {
					$this->StartRec = ($PageNo-1)*$this->DisplayRecs+1;
					if ($this->StartRec <= 0) {
						$this->StartRec = 1;
					} elseif ($this->StartRec >= intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1) {
						$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1;
					}
					$this->setStartRecordNumber($this->StartRec);
				}
			}
		}
		$this->StartRec = $this->getStartRecordNumber();

		// Check if correct start record counter
		if (!is_numeric($this->StartRec) || $this->StartRec == "") { // Avoid invalid start record counter
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} elseif (intval($this->StartRec) > intval($this->TotalRecs)) { // Avoid starting record > total records
			$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to last page first record
			$this->setStartRecordNumber($this->StartRec);
		} elseif (($this->StartRec-1) % $this->DisplayRecs <> 0) {
			$this->StartRec = intval(($this->StartRec-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to page boundary
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm;

		// Get upload data
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->id->FldIsDetailKey)
			$this->id->setFormValue($objForm->GetValue("x_id"));
		if (!$this->original_image->FldIsDetailKey) {
			$this->original_image->setFormValue($objForm->GetValue("x_original_image"));
		}
		if (!$this->thumbnail_image->FldIsDetailKey) {
			$this->thumbnail_image->setFormValue($objForm->GetValue("x_thumbnail_image"));
		}
		if (!$this->ip_address->FldIsDetailKey) {
			$this->ip_address->setFormValue($objForm->GetValue("x_ip_address"));
		}
		if (!$this->advertise_id->FldIsDetailKey) {
			$this->advertise_id->setFormValue($objForm->GetValue("x_advertise_id"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->id->CurrentValue = $this->id->FormValue;
		$this->original_image->CurrentValue = $this->original_image->FormValue;
		$this->thumbnail_image->CurrentValue = $this->thumbnail_image->FormValue;
		$this->ip_address->CurrentValue = $this->ip_address->FormValue;
		$this->advertise_id->CurrentValue = $this->advertise_id->FormValue;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id->setDbValue($rs->fields('id'));
		$this->original_image->setDbValue($rs->fields('original_image'));
		$this->thumbnail_image->setDbValue($rs->fields('thumbnail_image'));
		$this->ip_address->setDbValue($rs->fields('ip_address'));
		$this->advertise_id->setDbValue($rs->fields('advertise_id'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->original_image->DbValue = $row['original_image'];
		$this->thumbnail_image->DbValue = $row['thumbnail_image'];
		$this->ip_address->DbValue = $row['ip_address'];
		$this->advertise_id->DbValue = $row['advertise_id'];
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// original_image
		// thumbnail_image
		// ip_address
		// advertise_id

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// original_image
			$this->original_image->ViewValue = $this->original_image->CurrentValue;
			$this->original_image->ViewCustomAttributes = "";

			// thumbnail_image
			$this->thumbnail_image->ViewValue = $this->thumbnail_image->CurrentValue;
			$this->thumbnail_image->ViewCustomAttributes = "";

			// ip_address
			$this->ip_address->ViewValue = $this->ip_address->CurrentValue;
			$this->ip_address->ViewCustomAttributes = "";

			// advertise_id
			$this->advertise_id->ViewValue = $this->advertise_id->CurrentValue;
			$this->advertise_id->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

			// original_image
			$this->original_image->LinkCustomAttributes = "";
			$this->original_image->HrefValue = "";
			$this->original_image->TooltipValue = "";

			// thumbnail_image
			$this->thumbnail_image->LinkCustomAttributes = "";
			$this->thumbnail_image->HrefValue = "";
			$this->thumbnail_image->TooltipValue = "";

			// ip_address
			$this->ip_address->LinkCustomAttributes = "";
			$this->ip_address->HrefValue = "";
			$this->ip_address->TooltipValue = "";

			// advertise_id
			$this->advertise_id->LinkCustomAttributes = "";
			$this->advertise_id->HrefValue = "";
			$this->advertise_id->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// id
			$this->id->EditCustomAttributes = "";
			$this->id->EditValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// original_image
			$this->original_image->EditCustomAttributes = "";
			$this->original_image->EditValue = ew_HtmlEncode($this->original_image->CurrentValue);
			$this->original_image->PlaceHolder = ew_RemoveHtml($this->original_image->FldCaption());

			// thumbnail_image
			$this->thumbnail_image->EditCustomAttributes = "";
			$this->thumbnail_image->EditValue = ew_HtmlEncode($this->thumbnail_image->CurrentValue);
			$this->thumbnail_image->PlaceHolder = ew_RemoveHtml($this->thumbnail_image->FldCaption());

			// ip_address
			$this->ip_address->EditCustomAttributes = "";
			$this->ip_address->EditValue = ew_HtmlEncode($this->ip_address->CurrentValue);
			$this->ip_address->PlaceHolder = ew_RemoveHtml($this->ip_address->FldCaption());

			// advertise_id
			$this->advertise_id->EditCustomAttributes = "";
			$this->advertise_id->EditValue = ew_HtmlEncode($this->advertise_id->CurrentValue);
			$this->advertise_id->PlaceHolder = ew_RemoveHtml($this->advertise_id->FldCaption());

			// Edit refer script
			// id

			$this->id->HrefValue = "";

			// original_image
			$this->original_image->HrefValue = "";

			// thumbnail_image
			$this->thumbnail_image->HrefValue = "";

			// ip_address
			$this->ip_address->HrefValue = "";

			// advertise_id
			$this->advertise_id->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!$this->original_image->FldIsDetailKey && !is_null($this->original_image->FormValue) && $this->original_image->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->original_image->FldCaption());
		}
		if (!$this->thumbnail_image->FldIsDetailKey && !is_null($this->thumbnail_image->FormValue) && $this->thumbnail_image->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->thumbnail_image->FldCaption());
		}
		if (!$this->ip_address->FldIsDetailKey && !is_null($this->ip_address->FormValue) && $this->ip_address->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->ip_address->FldCaption());
		}
		if (!$this->advertise_id->FldIsDetailKey && !is_null($this->advertise_id->FormValue) && $this->advertise_id->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->advertise_id->FldCaption());
		}
		if (!ew_CheckInteger($this->advertise_id->FormValue)) {
			ew_AddMessage($gsFormError, $this->advertise_id->FldErrMsg());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Update record based on key values
	function EditRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE)
			return FALSE;
		if ($rs->EOF) {
			$EditRow = FALSE; // Update Failed
		} else {

			// Save old values
			$rsold = &$rs->fields;
			$this->LoadDbValues($rsold);
			$rsnew = array();

			// original_image
			$this->original_image->SetDbValueDef($rsnew, $this->original_image->CurrentValue, "", $this->original_image->ReadOnly);

			// thumbnail_image
			$this->thumbnail_image->SetDbValueDef($rsnew, $this->thumbnail_image->CurrentValue, "", $this->thumbnail_image->ReadOnly);

			// ip_address
			$this->ip_address->SetDbValueDef($rsnew, $this->ip_address->CurrentValue, "", $this->ip_address->ReadOnly);

			// advertise_id
			$this->advertise_id->SetDbValueDef($rsnew, $this->advertise_id->CurrentValue, 0, $this->advertise_id->ReadOnly);

			// Call Row Updating event
			$bUpdateRow = $this->Row_Updating($rsold, $rsnew);
			if ($bUpdateRow) {
				$conn->raiseErrorFn = 'ew_ErrorFn';
				if (count($rsnew) > 0)
					$EditRow = $this->Update($rsnew, "", $rsold);
				else
					$EditRow = TRUE; // No field to update
				$conn->raiseErrorFn = '';
				if ($EditRow) {
				}
			} else {
				if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

					// Use the message, do nothing
				} elseif ($this->CancelMessage <> "") {
					$this->setFailureMessage($this->CancelMessage);
					$this->CancelMessage = "";
				} else {
					$this->setFailureMessage($Language->Phrase("UpdateCancelled"));
				}
				$EditRow = FALSE;
			}
		}

		// Call Row_Updated event
		if ($EditRow)
			$this->Row_Updated($rsold, $rsnew);
		$rs->Close();
		return $EditRow;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "imageslist.php", $this->TableVar, TRUE);
		$PageId = "edit";
		$Breadcrumb->Add("edit", $PageId, ew_CurrentUrl());
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($images_edit)) $images_edit = new cimages_edit();

// Page init
$images_edit->Page_Init();

// Page main
$images_edit->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$images_edit->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var images_edit = new ew_Page("images_edit");
images_edit.PageID = "edit"; // Page ID
var EW_PAGE_ID = images_edit.PageID; // For backward compatibility

// Form object
var fimagesedit = new ew_Form("fimagesedit");

// Validate form
fimagesedit.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);
	this.PostAutoSuggest();
	if ($fobj.find("#a_confirm").val() == "F")
		return true;
	var elm, felm, uelm, addcnt = 0;
	var $k = $fobj.find("#" + this.FormKeyCountName); // Get key_count
	var rowcnt = ($k[0]) ? parseInt($k.val(), 10) : 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // Check rowcnt == 0 => Inline-Add
	var gridinsert = $fobj.find("#a_list").val() == "gridinsert";
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = ($k[0]) ? String(i) : "";
		$fobj.data("rowindex", infix);
			elm = this.GetElements("x" + infix + "_original_image");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($images->original_image->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_thumbnail_image");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($images->thumbnail_image->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_ip_address");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($images->ip_address->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_advertise_id");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($images->advertise_id->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_advertise_id");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($images->advertise_id->FldErrMsg()) ?>");

			// Set up row object
			ew_ElementsToRow(fobj);

			// Fire Form_CustomValidate event
			if (!this.Form_CustomValidate(fobj))
				return false;
	}

	// Process detail forms
	var dfs = $fobj.find("input[name='detailpage']").get();
	for (var i = 0; i < dfs.length; i++) {
		var df = dfs[i], val = df.value;
		if (val && ewForms[val])
			if (!ewForms[val].Validate())
				return false;
	}
	return true;
}

// Form_CustomValidate event
fimagesedit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fimagesedit.ValidateRequired = true;
<?php } else { ?>
fimagesedit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php $images_edit->ShowPageHeader(); ?>
<?php
$images_edit->ShowMessage();
?>
<form name="fimagesedit" id="fimagesedit" class="ewForm form-inline" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="images">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table class="ewGrid"><tr><td>
<table id="tbl_imagesedit" class="table table-bordered table-striped">
<?php if ($images->id->Visible) { // id ?>
	<tr id="r_id">
		<td><span id="elh_images_id"><?php echo $images->id->FldCaption() ?></span></td>
		<td<?php echo $images->id->CellAttributes() ?>>
<span id="el_images_id" class="control-group">
<span<?php echo $images->id->ViewAttributes() ?>>
<?php echo $images->id->EditValue ?></span>
</span>
<input type="hidden" data-field="x_id" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($images->id->CurrentValue) ?>">
<?php echo $images->id->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($images->original_image->Visible) { // original_image ?>
	<tr id="r_original_image">
		<td><span id="elh_images_original_image"><?php echo $images->original_image->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $images->original_image->CellAttributes() ?>>
<span id="el_images_original_image" class="control-group">
<input type="text" data-field="x_original_image" name="x_original_image" id="x_original_image" size="30" maxlength="250" placeholder="<?php echo ew_HtmlEncode($images->original_image->PlaceHolder) ?>" value="<?php echo $images->original_image->EditValue ?>"<?php echo $images->original_image->EditAttributes() ?>>
</span>
<?php echo $images->original_image->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($images->thumbnail_image->Visible) { // thumbnail_image ?>
	<tr id="r_thumbnail_image">
		<td><span id="elh_images_thumbnail_image"><?php echo $images->thumbnail_image->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $images->thumbnail_image->CellAttributes() ?>>
<span id="el_images_thumbnail_image" class="control-group">
<input type="text" data-field="x_thumbnail_image" name="x_thumbnail_image" id="x_thumbnail_image" size="30" maxlength="250" placeholder="<?php echo ew_HtmlEncode($images->thumbnail_image->PlaceHolder) ?>" value="<?php echo $images->thumbnail_image->EditValue ?>"<?php echo $images->thumbnail_image->EditAttributes() ?>>
</span>
<?php echo $images->thumbnail_image->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($images->ip_address->Visible) { // ip_address ?>
	<tr id="r_ip_address">
		<td><span id="elh_images_ip_address"><?php echo $images->ip_address->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $images->ip_address->CellAttributes() ?>>
<span id="el_images_ip_address" class="control-group">
<input type="text" data-field="x_ip_address" name="x_ip_address" id="x_ip_address" size="30" maxlength="50" placeholder="<?php echo ew_HtmlEncode($images->ip_address->PlaceHolder) ?>" value="<?php echo $images->ip_address->EditValue ?>"<?php echo $images->ip_address->EditAttributes() ?>>
</span>
<?php echo $images->ip_address->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($images->advertise_id->Visible) { // advertise_id ?>
	<tr id="r_advertise_id">
		<td><span id="elh_images_advertise_id"><?php echo $images->advertise_id->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $images->advertise_id->CellAttributes() ?>>
<span id="el_images_advertise_id" class="control-group">
<input type="text" data-field="x_advertise_id" name="x_advertise_id" id="x_advertise_id" size="30" placeholder="<?php echo ew_HtmlEncode($images->advertise_id->PlaceHolder) ?>" value="<?php echo $images->advertise_id->EditValue ?>"<?php echo $images->advertise_id->EditAttributes() ?>>
</span>
<?php echo $images->advertise_id->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("EditBtn") ?></button>
</form>
<script type="text/javascript">
fimagesedit.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$images_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$images_edit->Page_Terminate();
?>
