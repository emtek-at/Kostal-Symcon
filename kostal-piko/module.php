<?
abstract class dxsEntry
{
    const powerStatus = "16780032";
    const powerActual = "67109120";
    const outputAll = "251658753";
    const outputDay = "251658754";
    const l1Voltage = "67109378";
    const l1Power = "67109379";
    const l2Voltage = "67109634";
    const l2Power = "67109635";
    const l3Voltage = "67109890";
    const l3Power = "67109891";
    const s1Voltage = "33555202";
    const s1Current = "33555201";
    const s2Voltage = "33555458";
    const s2Current = "33555457";
    const s3Voltage = "33555714";
    const s3Current = "33555713";
}

// Klassendefinition
class kostalPico extends IPSModule {
    const debug = false;

    // Der Konstruktor des Moduls
    // Überschreibt den Standard Kontruktor von IPS
    public function __construct($InstanceID) {
        // Diese Zeile nicht löschen
        parent::__construct($InstanceID);
    }

    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->RegisterPropertyString("model", "p30");
        $this->RegisterPropertyString("host", "");
        $this->RegisterPropertyString("user", "pvserver");
        $this->RegisterPropertyString("password", "pvwr");
        $this->RegisterPropertyInteger("statusTimer", 15);

        $this->RegisterTimer("status_UpdateTimer", 0, 'KostalP_getStatus($_IPS[\'TARGET\']);');
    }

    // Überschreibt die intere IPS_ApplyChanges($id) Funktion
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();

        $model = $this->ReadPropertyString("model");
        $host = $this->ReadPropertyString("host");
        $user = $this->ReadPropertyString("user");
        $password = $this->ReadPropertyString("password");

        if(strlen($model) == 0) {
            $this->SetStatus(201);
        }else if(strlen($host) == 0){
            $this->SetStatus(202);
        }else if(strlen($user) == 0 && ($model == 'p55' || $model == 'p83' || $model == 'p30')){
            $this->SetStatus(203);
        }else if(strlen($password) == 0 && ($model == 'p55' || $model == 'p83' || $model == 'p30')){
            $this->SetStatus(204);
        }else{
            $this->RegisterVariableString("powerStatus", $this->Translate("varPowerStatus"), '', 1);
            $this->RegisterVariableFloat("powerActual", $this->Translate("varPowerActual"), "~Watt.3680", 2);
            $this->RegisterVariableFloat("outputAll", $this->Translate("varOutputAll"), "~Electricity", 3);
            $this->RegisterVariableFloat("outputDay", $this->Translate("varOutputDay"), "~Electricity", 4);

            switch ($this->getModelLineCount()){
                /** @noinspection PhpMissingBreakStatementInspection */
                case 3:
                    $this->RegisterVariableFloat("l3Voltage", $this->Translate("varL3Voltage"), "~Volt", 9);
                    $this->RegisterVariableFloat("l3Power", $this->Translate("varL3Power"), "~Watt.3680", 10);
                /** @noinspection PhpMissingBreakStatementInspection */
                case 2:
                    $this->RegisterVariableFloat("l2Voltage", $this->Translate("varL2Voltage"), "~Volt", 7);
                    $this->RegisterVariableFloat("l2Power", $this->Translate("varL2Power"), "~Watt.3680", 8);
                case 1:
                    $this->RegisterVariableFloat("l1Voltage", $this->Translate("varL1Voltage"), "~Volt", 5);
                    $this->RegisterVariableFloat("l1Power", $this->Translate("varL1Power"), "~Watt.3680", 6);
                    break;
            }

            switch($this->getModelStringCount()){
                /** @noinspection PhpMissingBreakStatementInspection */
                case 3:
                    $this->RegisterVariableFloat("s3Voltage", $this->Translate("varS3Voltage"), "~Volt", 15);
                    $this->RegisterVariableFloat("s3Current", $this->Translate("varS3Current"), "~Ampere", 16);
                /** @noinspection PhpMissingBreakStatementInspection */
                case 2:
                    $this->RegisterVariableFloat("s2Voltage", $this->Translate("varS2Voltage"), "~Volt", 13);
                    $this->RegisterVariableFloat("s2Current", $this->Translate("varS2Current"), "~Ampere", 14);
                case 1:
                    $this->RegisterVariableFloat("s1Voltage", $this->Translate("varS1Voltage"), "~Volt", 11);
                    $this->RegisterVariableFloat("s1Current", $this->Translate("varS1Current"), "~Ampere", 12);
                    break;
            }

            $this->debug('host', $host);

            $statusInterval = $this->ReadPropertyInteger("statusTimer");
            $this->debug('Update Status Interval', $statusInterval.' sec');

            $this->SetTimerInterval('status_UpdateTimer', $statusInterval*1000);

            $this->SetStatus(102);
        }
    }

    public function getStatus()
    {
        $model = $this->ReadPropertyString("model");

        switch($model){
            case 'p30':
                $this->parsePiko30();
                break;
            case 'p55':
                $this->parsePiko55();
                break;
            case 'p83':
                $this->parsePiko83();
                break;
            case 'p12':
                // this model supports json requests
                $host = $this->ReadPropertyString("host");
                $dxsEntries = array();
                $dxsEntries[] = dxsEntry::powerStatus;
                $dxsEntries[] = dxsEntry::powerActual;
                $dxsEntries[] = dxsEntry::outputAll;
                $dxsEntries[] = dxsEntry::outputDay;
                switch($this->getModelLineCount()){
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 3:
                        $dxsEntries[] = dxsEntry::l3Voltage;
                        $dxsEntries[] = dxsEntry::l3Power;
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 2:
                        $dxsEntries[] = dxsEntry::l2Voltage;
                        $dxsEntries[] = dxsEntry::l2Power;
                    case 1:
                        $dxsEntries[] = dxsEntry::l1Voltage;
                        $dxsEntries[] = dxsEntry::l1Power;
                        break;
                }
                switch ($this->getModelStringCount()){
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 3:
                        $dxsEntries[] = dxsEntry::s3Voltage;
                        $dxsEntries[] = dxsEntry::s3Current;
                    /** @noinspection PhpMissingBreakStatementInspection */
                    case 2:
                        $dxsEntries[] = dxsEntry::s2Voltage;
                        $dxsEntries[] = dxsEntry::s2Current;
                    case 1:
                        $dxsEntries[] = dxsEntry::s1Voltage;
                        $dxsEntries[] = dxsEntry::s1Current;
                        break;
                }

                $this->parsePikoDxs($host, $dxsEntries);
                break;
        }
    }

    private function parsePiko30(){
        /*
         * function is from https://github.com/hermanthegerman2/KostalPiko
         */
        $host = $this->ReadPropertyString("host");
        $user = $this->ReadPropertyString("user");
        $password = $this->ReadPropertyString("password");
        $url = 'http://'.$user.':'.$password.'@'.$host;

        $output = file_get_contents($url, "r");

        //AC-Leistung_Aktuell
        $pos1 = strpos($output, "aktuell</td>");
        $pos2 = strpos($output, "&nbsp;</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 61), $pos2 - $pos1 - 61);
        SetValue($this->GetIDForIdent("powerActual"), $data=="x x x" ? 0 : (float)$data);

        //AC_Leistung_Status
        $pos1 = strpos($output, "Status</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 17);
        $data = substr($output, ($pos1 + 29), $pos2 - $pos1 - 29);
        SetValue($this->GetIDForIdent("powerStatus"), $data=="x x x" ? 0 : $data);

        //Energie_Gesamtertrag
        $pos1 = strpos($output, "Gesamtenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 30);
        $data = substr($output, ($pos1 + 67), $pos2 - $pos1 - 67);
        SetValue($this->GetIDForIdent("outputAll"), $data=="x x x" ? 0 : (float)$data);

        //Energie_Tagesertrag_Aktuell
        $pos1 = strpos($output, "Tagesenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("outputDay"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 64), $pos2 - $pos1 - 64);
        SetValue($this->GetIDForIdent("s1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 64), $pos2 - $pos1 - 64);
        SetValue($this->GetIDForIdent("l1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 61), $pos2 - $pos1 - 61);
        SetValue($this->GetIDForIdent("s1Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 64), $pos2 - $pos1 - 64);
        SetValue($this->GetIDForIdent("l1Power"), $data=="x x x" ? 0 : (float)$data);
    }
    private function parsePiko55(){
        /*
         * function is from https://github.com/hermanthegerman2/KostalPiko
         */
        $host = $this->ReadPropertyString("host");
        $user = $this->ReadPropertyString("user");
        $password = $this->ReadPropertyString("password");
        $url = 'http://'.$user.':'.$password.'@'.$host;

        $output = file_get_contents($url, "r");

        //AC-Leistung_Aktuell
        $pos1 = strpos($output, "aktuell</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 65), $pos2 - $pos1 - 65);
        SetValue($this->GetIDForIdent("powerActual"), $data=="x x x" ? 0 : (float)$data);

        //AC_Leistung_Status
        $pos1 = strpos($output, "Status</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 33), $pos2 - $pos1 - 33);
        SetValue($this->GetIDForIdent("powerStatus"), $data=="x x x" ? 0 : $data);

        //Energie_Gesamtertrag
        $pos1 = strpos($output, "Gesamtenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 30);
        $data = substr($output, ($pos1 + 70), $pos2 - $pos1 - 70);
        SetValue($this->GetIDForIdent("outputAll"), $data=="x x x" ? 0 : (float)$data);

        //Energie_Tagesertrag_Aktuell
        $pos1 = strpos($output, "Tagesenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 70), $pos2 - $pos1 - 70);
        SetValue($this->GetIDForIdent("outputDay"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("s1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 63), $pos2 - $pos1 - 63);
        SetValue($this->GetIDForIdent("s1Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l1Power"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String2_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("s2Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L2_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l2Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String2_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 63), $pos2 - $pos1 - 63);
        SetValue($this->GetIDForIdent("s2Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L2_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l2Power"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String3_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output,($pos1+66),$pos2-$pos1-66);
        SetValue($this->GetIDForIdent("s3Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L3_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l3Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String3_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output,($pos1+63),$pos2-$pos1-63);
        SetValue($this->GetIDForIdent("s3Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L3_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l3Power"), $data=="x x x" ? 0 : (float)$data);
    }
    private function parsePiko83(){
        $host = $this->ReadPropertyString("host");
        $user = $this->ReadPropertyString("user");
        $password = $this->ReadPropertyString("password");
        $url = 'http://'.$user.':'.$password.'@'.$host;

        $output = file_get_contents($url, "r");

        //AC-Leistung_Aktuell
        $pos1 = strpos($output, "aktuell</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 65), $pos2 - $pos1 - 65);
        SetValue($this->GetIDForIdent("powerActual"), $data=="x x x" ? 0 : (float)$data);

        //AC_Leistung_Status
        $pos1 = strpos($output, "Status</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 33), $pos2 - $pos1 - 33);
        SetValue($this->GetIDForIdent("powerStatus"), $data=="x x x" ? 0 : $data);

        //Energie_Gesamtertrag
        $pos1 = strpos($output, "Gesamtenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 30);
        $data = substr($output, ($pos1 + 70), $pos2 - $pos1 - 70);
        SetValue($this->GetIDForIdent("outputAll"), $data=="x x x" ? 0 : (float)$data);

        //Energie_Tagesertrag_Aktuell
        $pos1 = strpos($output, "Tagesenergie</td>");
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 70), $pos2 - $pos1 - 70);
        SetValue($this->GetIDForIdent("outputDay"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("s1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l1Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String1_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 63), $pos2 - $pos1 - 63);
        SetValue($this->GetIDForIdent("s1Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L1_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l1Power"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String2_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("s2Voltage"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L2_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l2Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String2_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 63), $pos2 - $pos1 - 63);
        SetValue($this->GetIDForIdent("s2Current"), $data=="x x x" ? 0 : (float)$data);

        //Ausgangsleistung_L2_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l2Power"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String3_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);

        //Ausgangsleistung_L3_Spannung
        $pos1 = strpos($output, "Spannung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l3Voltage"), $data=="x x x" ? 0 : (float)$data);

        //PV_Generator_String3_Strom
        $pos1 = strpos($output, "Strom</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);

        //Ausgangsleistung_L3_Leistung
        $pos1 = strpos($output, "Leistung</td>", $pos2);
        $pos2 = strpos($output, "</td>", $pos1 + 20);
        $data = substr($output, ($pos1 + 66), $pos2 - $pos1 - 66);
        SetValue($this->GetIDForIdent("l3Power"), $data=="x x x" ? 0 : (float)$data);
    }

    private function parsePikoDxs($host, $dxsEntries){
        $url = 'http://'.$host.'/api/dxs.json?';

        foreach($dxsEntries as $entry){
            $url .= 'dxsEntries='.$entry.'&';
        }
        $url = trim($url, '&');

        $output = file_get_contents($url, "r");
        $arr = json_decode($output, true);

        foreach($arr['dxsEntries'] as $entry){
            switch($entry['dxsId']){
                case dxsEntry::powerStatus:
                    switch(intval($entry['value'])){
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                            SetValue($this->GetIDForIdent("powerStatus"), $this->Translate("varPowerStatus".intval($entry['value'])));
                            break;
                        default:
                            SetValue($this->GetIDForIdent("powerStatus"), 'unknown ('.$entry['value'].')');
                            break;
                    }
                    break;
                case dxsEntry::powerActual:
                    SetValue($this->GetIDForIdent("powerActual"), floatval($entry['value']));
                    break;
                case dxsEntry::outputAll:
                    SetValue($this->GetIDForIdent("outputAll"), floatval($entry['value']));
                    break;
                case dxsEntry::outputDay:
                    SetValue($this->GetIDForIdent("outputDay"), floatval($entry['value'])/1000);
                    break;
                case dxsEntry::l1Voltage:
                    SetValue($this->GetIDForIdent("l1Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::l1Power:
                    SetValue($this->GetIDForIdent("l1Power"), floatval($entry['value']));
                    break;
                case dxsEntry::l2Voltage:
                    SetValue($this->GetIDForIdent("l2Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::l2Power:
                    SetValue($this->GetIDForIdent("l2Power"), floatval($entry['value']));
                    break;
                case dxsEntry::l3Voltage:
                    SetValue($this->GetIDForIdent("l3Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::l3Power:
                    SetValue($this->GetIDForIdent("l3Power"), floatval($entry['value']));
                    break;
                case dxsEntry::s1Voltage:
                    SetValue($this->GetIDForIdent("s1Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::s1Current:
                    SetValue($this->GetIDForIdent("s1Current"), floatval($entry['value']));
                    break;
                case dxsEntry::s2Voltage:
                    SetValue($this->GetIDForIdent("s2Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::s2Current:
                    SetValue($this->GetIDForIdent("s2Current"), floatval($entry['value']));
                    break;
                case dxsEntry::s3Voltage:
                    SetValue($this->GetIDForIdent("s3Voltage"), floatval($entry['value']));
                    break;
                case dxsEntry::s3Current:
                    SetValue($this->GetIDForIdent("s3Current"), floatval($entry['value']));
                    break;
            }
        }
    }

    private function getModelLineCount(){
        $val = 0;

        switch($model = $this->ReadPropertyString("model")){
            case 'p30':
                $val = 1;
                break;
            case 'p55':
                $val = 3;
                break;
            case 'p83':
                $val = 3;
                break;
            case 'p12':
                $val = 3;
                break;
        }

        return $val;
    }
    private function getModelStringCount(){
        $val = 0;

        switch($model = $this->ReadPropertyString("model")){
            case 'p30':
                $val = 1;
                break;
            case 'p55':
                $val = 3;
                break;
            case 'p83':
                $val = 2;
                break;
            case 'p12':
                $val = 2;
                break;
        }

        return $val;
    }


    private function debug($name, $data){
        if(self::debug)
            $this->SendDebug($name, $data, 0);
    }
}