<?php

namespace App\Services;

use PDO;
use PDOException;
use Throwable;

use function Laravel\Prompts\error;

class OracleService
{
    protected $conn;

    public function __construct()
    {
        try {
            $this->conn = new PDO(
                env('DB_DSN_ORACLE_PDO', ''),
                env('DB_USERNAME_ORACLE_PDO', ''),
                env('DB_PASSWORD_ORACLE_PDO', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception('Error connection Oracle (details 1): ' . $e->getMessage());
        }
    }

    public function getWorkcenterToSync(): array
    {
        try {
            $sql = '
            SELECT
                WC.CONTRACT,
                WC.DEPARTMENT_NO,
                IFSAPP.WORK_CENTER_DEPARTMENT_API.Get_Description(WC.CONTRACT, WC.DEPARTMENT_NO) AS DEPARTMENT_NAME,
                WC.PRODUCTION_LINE,
                NVL(PL.CF$_LONG_DESCRIPTION, PL.DESCRIPTION) AS PROD_LINE_NAME,
                WR.WORK_CENTER_NO AS MAIN_WORKCENTER,
                WR.RESOURCE_ID AS WORK_CENTER_NO,
                WR.DESCRIPTION,
                NVL(WC.CF$_CRITICAL_DB, \'N\') AS CRITICAL_WORKCENTER
            FROM ifsapp.WORK_CENTER_CFV WC
            LEFT JOIN ifsapp.PRODUCTION_LINE_CFV PL
                ON WC.CONTRACT = PL.CONTRACT
                AND WC.PRODUCTION_LINE = PL.PRODUCTION_LINE
            LEFT JOIN IFSAPP.WORK_CENTER_RESOURCE WR
                ON WC.CONTRACT = WR.CONTRACT
                AND WC.WORK_CENTER_NO = WR.WORK_CENTER_NO
            LEFT JOIN (
                SELECT WR.CONTRACT, WR.WORK_CENTER_NO, COUNT(RESOURCE_ID) as NUM_RESOURCES
                FROM IFSAPP.WORK_CENTER_RESOURCE WR
                GROUP BY WR.CONTRACT, WR.WORK_CENTER_NO
            ) t1
                ON t1.CONTRACT = WC.CONTRACT
                AND t1.WORK_CENTER_NO = WC.WORK_CENTER_NO
            WHERE WC.OBJSTATE = \'Active\'
            AND (t1.NUM_RESOURCES = 1 OR WR.WORK_CENTER_NO <> WR.RESOURCE_ID)
            ORDER BY WC.CONTRACT, WC.DEPARTMENT_NO, WC.PRODUCTION_LINE
            ';


            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Throwable $e) {
            throw new \Exception('Error get Workcenter To Sync (getWorkcenterToSync): ' . $e->getMessage());
        }

    }

    public function getDowntimeCauses(): array
    {
        try {

            $sql = 'SELECT DOWNTIME_CAUSE_ID, DESCRIPTION, "CF$_GLOBAL_DOWNTIME" AS GLOBAL_DOWNTIME FROM IFSAPP.MACHINE_DOWNTIME_CAUSE_CFV ORDER BY GLOBAL_DOWNTIME DESC, DOWNTIME_CAUSE_ID ASC';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            throw new \Exception('Error fetching downtime causes (details 2): ' . $e->getMessage());
        }
    }

    public function getWorkcenterLocations(string $workcenterCode): array
    {
        try {
            $sql = "SELECT LOCATION_NO FROM IFSAPP.WORK_CENTER_LOCATION WHERE WORK_CENTER_NO = :workcenterCode";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':workcenterCode', $workcenterCode);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            throw new \Exception('Error fetching locations: ' . $e->getMessage());
        }
    }

    public function getInventoryParts(string $partnoId, string $contract = 'BTP'): array
    {
        try {
            $sql = '
                WITH PARTS AS (
                    SELECT
                        PART_NO AS ID,
                        DESCRIPTION,
                        ROW_NUMBER() OVER (PARTITION BY PART_NO ORDER BY DESCRIPTION) AS RN
                    FROM IFSAPP.INVENTORY_PART
                    WHERE PART_NO LIKE :partnoId
                    AND CONTRACT = :contract
                )
                SELECT
                    ID,
                    DESCRIPTION
                FROM PARTS
                WHERE RN = 1
            ';

            $likePartNo = strtoupper($partnoId) . '%';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':partnoId', $likePartNo);
            $stmt->bindParam(':contract', $contract);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }

    }

    public function getWorkcenterShopOrdersList(string $workcenterCode, string $contract = 'BTP' ): array
    {
        try {
            $sql = "
                SELECT
                    d.OP_ID,
                    d.OPERATION_NO,
                    d.ORDER_NO,
                    d.RELEASE_NO,
                    d.SEQUENCE_NO,
                    d.WORK_CENTER_NO,
                    d.RESOURCE_ID,
                    d.OP_START_DATE AS PLANNED_START_DATE,
                    d.START_TIME,
                    d.TIME_TYPE_DB,
                    ifsapp.shop_ord_util_api.get_objstate(d.order_no, d.release_no, d.sequence_no) AS SHOP_ORDER_STATE,
                    d.OPER_STATUS_CODE_DB AS OPERATION_STATE_CODE,
                    d.OPER_STATUS_CODE AS OPERATION_STATE,
                    d.REVISED_QTY_DUE,
                    d.QTY_COMPLETE,
                    d.QTY_SCRAPPED,
                    IFSAPP.INVENTORY_PART_API.Get_Unit_Meas(d.contract, d.PART_NO) AS UNIT_MEAS,
                    d.OBJVERSION,
                    d.OBJKEY
                FROM IFSAPP.SO_OPER_DISPATCH_LIST d
                WHERE d.WORK_CENTER_NO = :workcenterCode
                AND d.CONTRACT = :contract
                AND d.BOM_TYPE_DB = 'M'
                AND ifsapp.shop_ord_util_api.get_objstate(d.order_no, d.release_no, d.sequence_no) NOT IN ('Closed', 'Cancelled', 'Parked')
                AND d.oper_status_code_db <> '90'
                AND (
                    NOT EXISTS (
                    SELECT 1
                    FROM shop_operation_load s1
                    WHERE s1.order_no = d.order_no
                        AND s1.release_no = d.release_no
                        AND s1.sequence_no = d.sequence_no
                        AND s1.operation_no = d.operation_no
                    )
                    OR
                    NOT EXISTS (
                    SELECT 1
                    FROM shop_operation_load s2
                    WHERE s2.order_no = d.order_no
                        AND s2.release_no = d.release_no
                        AND s2.sequence_no = d.sequence_no
                        AND TO_NUMBER(s2.operation_no) < TO_NUMBER(d.operation_no)
                        AND NVL(s2.op_qty_complete, 0) = 0
                    )
                )
                ORDER BY d.START_TIME ASC, d.OP_START_DATE DESC
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':workcenterCode', $workcenterCode);
            $stmt->bindParam(':contract', $contract);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }
    }

    public function getWorkcenterShopOrdersPartNo(string $op_id)
    {

        try {
            $sql = '
                SELECT IP.PART_NO, IP.DESCRIPTION, IP.UNIT_MEAS,
                       SOO.*,
                       ifsapp.shop_ord_util_api.get_objstate(SOO.order_no, SOO.release_no, SOO.sequence_no) AS SHOP_ORDER_STATE
                  FROM IFSAPP.SHOP_ORDER_OPERATION SOO
                 INNER JOIN IFSAPP.INVENTORY_PART IP ON IP.PART_NO = SOO.PART_NO
                                                    AND IP.CONTRACT =  SOO.CONTRACT
                 WHERE SOO.OP_ID = :op_id
            ';

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':op_id', $op_id);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }

    }

    public function findShopOrder (string $order_no, string $release_no, string $sequence_no, string $workcenter_array_code)
    {

    try {
    $sql = '
        SELECT 
            SOO.*, 
            IP.PART_NO, 
            IP.DESCRIPTION, 
            IP.UNIT_MEAS,
            ifsapp.shop_ord_util_api.get_objstate(SOO.order_no, SOO.release_no, SOO.sequence_no) AS SHOP_ORDER_STATE
        FROM IFSAPP.SO_OPER_DISPATCH_LIST SOO
        INNER JOIN IFSAPP.INVENTORY_PART IP 
            ON IP.PART_NO = SOO.PART_NO
            AND IP.CONTRACT = SOO.CONTRACT
        WHERE SOO.WORK_CENTER_NO IN ('.$workcenter_array_code.')
            AND SOO.ORDER_NO = :order_no
            AND SOO.RELEASE_NO = :release_no
            AND SOO.SEQUENCE_NO = :sequence_no
            AND SOO.BOM_TYPE_DB = \'M\'
            AND ifsapp.shop_ord_util_api.get_objstate(SOO.order_no, SOO.release_no, SOO.sequence_no) NOT IN (\'Closed\', \'Cancelled\', \'Parked\')
            AND SOO.OPER_STATUS_CODE_DB <> \'90\'
            AND (
                NOT EXISTS (
                    SELECT 1 
                    FROM shop_operation_load s1
                    WHERE 
                        s1.order_no = SOO.order_no
                        AND s1.release_no = SOO.release_no
                        AND s1.sequence_no = SOO.sequence_no
                        AND s1.operation_no = SOO.operation_no
                )
                OR
                NOT EXISTS (
                    SELECT 1 
                    FROM shop_operation_load s2
                    WHERE 
                        s2.order_no = SOO.order_no
                        AND s2.release_no = SOO.release_no
                        AND s2.sequence_no = SOO.sequence_no
                        AND TO_NUMBER(s2.operation_no) < TO_NUMBER(SOO.operation_no)
                        AND NVL(s2.op_qty_complete, 0) = 0
                )
            )
    ';

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':order_no', $order_no);
        $stmt->bindParam(':release_no', $release_no);
        $stmt->bindParam(':sequence_no', $sequence_no);
        //$stmt->bindParam(':workcenter_array_code', $workcenter_array_code);



        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception('Erro Oracle: ' . implode(' | ', $errorInfo));
        }

        $result = $stmt->fetchAll();
        return $result;

        } catch (Throwable $e) {
            throw new \Exception('Error saving shop order: ' . $e->getMessage());
        }
    }

    public function startProductionIFS(string $workcenterCode, string $contract, string $op_id)
    {

        $formattedDate = date('Y-m-d H:i:s');
        
        $lsMachineTime = 'TRUE';
        $lsLaborTime = 'FALSE';
        $dfnCrewSize = 0;
        $dfsClockingNoteText = '';
        $sGlobalCompany = '25';
        $sCurrentEmployeeId = '';
        $lsEmployeeIds = "!\n";
        $sTeamId = '';
        $sStopEmployeeClockings = 'FALSE';


        // format the op_id to match the expected format
        $op_id = "!\n\$OP_ID=" . $op_id . "\n";

        try {
            $sqlStartProduction = "
            DECLARE
                p0_ VARCHAR2(32000) := :op_id;
                p1_ DATE := TO_DATE(:currentDate, 'YYYY-MM-DD HH24:MI:SS');
                p2_ VARCHAR2(32000) := :lsMachineTime;
                p3_ VARCHAR2(32000) := :lsLaborTime;
                p4_ FLOAT := :dfnCrewSize;
                p5_ VARCHAR2(32000) := :dfsClockingNoteText;
                p6_ VARCHAR2(32000) := :sContract;
                p7_ VARCHAR2(32000) := :sWorkcenterNo;
                p8_ VARCHAR2(32000) := :cComboBoxResourceId;
                p9_ VARCHAR2(32000) := :sGlobalCompany;
                p10_ VARCHAR2(32000) := :sCurrentEmployeeId;
                p11_ VARCHAR2(32000) := :lsEmployeeIds;
                p12_ VARCHAR2(32000) := :sTeamId;
                p13_ VARCHAR2(32000) := :sStopEmployeeClockings;
            BEGIN
                IFSAPP.SHOP_OPER_CLOCKING_UTIL_API.Start_Operation(
                    p0_,
                    p1_,
                    p2_,
                    p3_,
                    p4_,
                    p5_,
                    'RUN TIME',
                    p6_,
                    p7_,
                    p8_,
                    p9_,
                    p10_,
                    p11_,
                    p12_,
                    p13_,
                    'TRUE'
                );
                COMMIT;
            END;
            ";

            $stmt = $this->conn->prepare($sqlStartProduction);

            $stmt->bindParam(':op_id', $op_id);
            $stmt->bindParam(':currentDate', $formattedDate); 
            $stmt->bindParam(':lsMachineTime', $lsMachineTime);
            $stmt->bindParam(':lsLaborTime', $lsLaborTime);
            $stmt->bindParam(':dfnCrewSize', $dfnCrewSize);
            $stmt->bindParam(':dfsClockingNoteText', $dfsClockingNoteText);
            $stmt->bindParam(':sContract', $contract);
            $stmt->bindParam(':sWorkcenterNo', $workcenterCode);
            $stmt->bindParam(':cComboBoxResourceId', $workcenterCode);
            $stmt->bindParam(':sGlobalCompany', $sGlobalCompany);
            $stmt->bindParam(':sCurrentEmployeeId', $sCurrentEmployeeId);
            $stmt->bindParam(':lsEmployeeIds', $lsEmployeeIds);
            $stmt->bindParam(':sTeamId', $sTeamId);
            $stmt->bindParam(':sStopEmployeeClockings', $sStopEmployeeClockings);

            $stmt->execute();

            return ['message' => 'ok', 'op_id' => $op_id];

        } catch (Throwable $e) {
            throw new \Exception('Error saving shop order: ' . $e->getMessage());
        }
    }

    public function finishProductionIFS ( string $order_no, string $release_no, string $sequence_no, string $workcenterCode, string $contract, string $operation_no)
    {
        $formattedDate = date('Y-m-d H:i:s');
        $sStopReasonClient = 'Partially Reported';
        $dfsClockingNote = '';
        $dfsInterruptionCause = '';
        $dfsInterruptionNote = '';
        $sGlobalCompany = '25';
        $sEmployeeId = '';
        $sTeamId = '';
        $dfnCrewSize = NULL;



        try {
            $sqlFinishProduction= "
            DECLARE 
                p0_ VARCHAR2(32000) := :sShopOrderNo;
                p1_ VARCHAR2(32000) := :sReleaseNo;
                p2_ VARCHAR2(32000) := :sSequenceNo;
                p3_ FLOAT := :nOperationNo;
                p4_ DATE:=TO_DATE(:dfdStopTime, 'YYYY-MM-DD HH24:MI:SS');
                p5_ VARCHAR2(32000) := :sStopReasonClient;
                p6_ VARCHAR2(32000) := :dfsClockingNote;
                p7_ VARCHAR2(32000) := :sContract;
                p8_ VARCHAR2(32000) := :dfsInterruptionCause;
                p9_ VARCHAR2(32000) := :dfsInterruptionNote;
                p10_ VARCHAR2(32000) := :sGlobalCompany;
                p11_ VARCHAR2(32000) := :sEmployeeId;
                p12_ VARCHAR2(32000) := :sTeamId;
                p13_ FLOAT := :dfnCrewSize;
            BEGIN 
            IFSAPP.Shop_Oper_Clocking_Util_API.Stop_Operation(p0_ , 
						p1_ , 
						p2_ , 
						p3_ , 
						p4_ , 
						p5_ , 
						p6_ , 
						p7_ , 
						p8_ , 
						p9_ , 
						p10_ , 
						p11_ , 
                        p12_ , 
                        'FALSE', 
                        'TRUE', 
                        p13_ );
            COMMIT;
            END;
            ";

            $stmt = $this->conn->prepare($sqlFinishProduction);

            $stmt->bindParam(':sShopOrderNo', $order_no);
            $stmt->bindParam(':sReleaseNo', $release_no);
            $stmt->bindParam(':sSequenceNo', $sequence_no);
            $stmt->bindParam(':nOperationNo', $operation_no);
            $stmt->bindParam(':dfdStopTime', $formattedDate );
            $stmt->bindParam(':sStopReasonClient', $sStopReasonClient);
            $stmt->bindParam(':dfsClockingNote', $dfsClockingNote);
            $stmt->bindParam(':sContract', $contract);
            $stmt->bindParam(':dfsInterruptionCause', $dfsInterruptionCause);
            $stmt->bindParam(':dfsInterruptionNote', $dfsInterruptionNote);
            $stmt->bindParam(':sGlobalCompany', $sGlobalCompany);
            $stmt->bindParam(':sEmployeeId', $sEmployeeId);
            $stmt->bindParam(':sTeamId', $sTeamId);
            $stmt->bindParam(':dfnCrewSize', $dfnCrewSize);

            $stmt->execute();

            return ['message' => 'ok finish', 'op_id' => $operation_no];

        } catch (Throwable $e) {
            throw new \Exception('Error finishing production: ' . $e->getMessage());
        }
    }

    public function startDowntimeIFS (string $contract, string $workcenterCode, string $reason, string $comment) {
        $formattedDate = date('Y-m-d H:i:s');
        $company = '25';
        $currentEmployeeId = '';
        $currentTeamId = '';

        try {
            $sqlStartDowntime= "
            DECLARE
                p0_ DATE := TO_DATE(:start_time_, 'YYYY-MM-DD HH24:MI:SS');
                p1_ VARCHAR2(32000) := :contract_;
                p2_ VARCHAR2(32000) := :work_center_no_;
                p3_ VARCHAR2(32000) := :resource_id_;
                p4_ VARCHAR2(32000) := :downtime_cause_id_;
                p5_ VARCHAR2(32000) := :note_text_;
                p6_ VARCHAR2(32000) := :company_;
                p7_ VARCHAR2(32000) := :current_employee_id_;
                p8_ VARCHAR2(32000) := :current_team_id_;
            BEGIN
            IFSAPP.Shop_Oper_Clocking_Util_API.Start_Machine_Downtime(p0_ , 
                p1_ , 
                p2_ , 
                p3_ , 
                p4_ , 
                p5_ , 
                p6_ , 
                p7_ , 
                p8_ );
            COMMIT;
            END;
            ";

            $stmt = $this->conn->prepare($sqlStartDowntime);

            $stmt->bindParam(':start_time_', $formattedDate);
            $stmt->bindParam(':contract_', $contract);
            $stmt->bindParam(':work_center_no_', $workcenterCode);
            $stmt->bindParam(':resource_id_', $workcenterCode);
            $stmt->bindParam(':downtime_cause_id_', $reason);
            $stmt->bindParam(':note_text_', $comment);
            $stmt->bindParam(':company_', $company);
            $stmt->bindParam(':current_employee_id_', $currentEmployeeId);
            $stmt->bindParam(':current_team_id_', $currentTeamId);

            $stmt->execute();
            return ['message' => 'ok downtime', 'reason' => $reason];

        } catch (Throwable $e) {
            throw new \Exception('Error starting downtime: ' . $e->getMessage());
        }
    }

    public function getfinishDowntimeIFS ( string $contract, string $workcenter_code) {
        
        try {
            $sql = '
                SELECT NOTE_TEXT, START_TIME, DOWNTIME_CAUSE_ID, FINISH_TIME 
                FROM IFSAPP.SHOP_FLOOR_CLOCKINGS_UIV 
                WHERE WORK_CENTER_NO = :workcenter_code
                AND RESOURCE_ID = :resource_id
                AND CONTRACT = :contract
                AND FINISH_TIME IS NULL
            ';

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':workcenter_code', $workcenter_code);
            $stmt->bindParam(':resource_id', $workcenter_code); // pode ser necessário mudar o resource_id
            $stmt->bindParam(':contract', $contract);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }
    }

    public function finishDowntimeIFS (string $contract, string $workcenter_code, string $comment) {
        $formattedDate = date('Y-m-d-H.i.s');
        $company = '25';
        $currentEmployeeId = '';
        $currentTeamId = '';
        $resource_id = $workcenter_code;
        
        try {
            $sqlFinishDowntime= "
            DECLARE
                p0_ VARCHAR2(32000) := :contract;
                p1_ VARCHAR2(32000) := :work_center_no;
                p2_ VARCHAR2(32000) := :resource_id;
                p3_ DATE := TO_DATE(:finish_time, 'YYYY-MM-DD-HH24.MI.SS', 'NLS_CALENDAR=GREGORIAN');
                p4_ VARCHAR2(32000) := :note_text;
                p5_ VARCHAR2(32000) := :company;
                p6_ VARCHAR2(32000) := :current_employee_id;
                p7_ VARCHAR2(32000) := :current_team_id;
            BEGIN
            IFSAPP.Shop_Oper_Clocking_Util_API.Stop_Machine_Downtime(p0_ , 
                p1_ , 
                p2_ , 
                p3_ , 
                p4_ , 
                p5_ , 
                p6_ , 
                p7_ );
            COMMIT;
            END;
            ";

            $stmt = $this->conn->prepare($sqlFinishDowntime);

            $stmt->bindParam(':contract', $contract);
            $stmt->bindParam(':work_center_no', $workcenter_code);
            $stmt->bindParam(':resource_id', $resource_id);
            $stmt->bindParam(':finish_time', $formattedDate);
            $stmt->bindParam(':note_text', $comment);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':current_employee_id', $currentEmployeeId);
            $stmt->bindParam(':current_team_id', $currentTeamId);

            $stmt->execute();
            return ['message' => 'ok downtime', 'comment' => $comment];

        } catch (Throwable $e) {
            throw new \Exception('Error finishing downtime: ' . $e->getMessage());
        }
    }

    // Raw Material 
    public function getShopOrderPartNo ( string $order_no, string $release_no, string $sequence_no, string $contract)
     {
        try {
            $sql = '
                SELECT
                    m.PART_NO,
                    m.LINE_ITEM_NO,
                    IFSAPP.Inventory_Part_API.Get_Description(contract, part_no) as description,
                    SUM(m.QTY_ISSUED) AS QTY_ISSUED,
                    SUM(m.QTY_REQUIRED) AS QTY_REQUIRED
                FROM IFSAPP.SHOP_MATERIAL_ALLOC_UIV m
                WHERE m.ORDER_NO     = :order_no
                AND m.RELEASE_NO   = :release_no
                AND m.SEQUENCE_NO  = :sequence_no
                AND m.CONTRACT     = :contract
                GROUP BY m.PART_NO, m.LINE_ITEM_NO, IFSAPP.Inventory_Part_API.Get_Description(contract, part_no)
                ORDER BY m.LINE_ITEM_NO ASC
            ';

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_no', $order_no);
            $stmt->bindParam(':release_no', $release_no);
            $stmt->bindParam(':sequence_no', $sequence_no);
            $stmt->bindParam(':contract', $contract);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }
    }

    public function getPnoComponentHistory (string $orderNo,string $releaseNo,string $sequenceNo,string $structureContract,string $partNo,string $lineNo) {
            
        try {
            $sql = "
                SELECT
                    m.LOT_BATCH_NO,
                    m.INVENTORY_QTY,
                    m.LOCATION_NO,
                    m.MATERIAL_HISTORY_ID,
                    TO_CHAR(m.TIME_STAMP, 'DD-MM-YYYY HH24:MI:SS') AS date_time
                FROM IFSAPP.MATERIAL_HISTORY m
                WHERE m.ORDER_REF1 = :order_no
                    AND m.ORDER_REF2 = :release_no
                    AND m.ORDER_REF3 = :sequence_no
                    AND m.CONTRACT = :contract
                    AND m.MATERIAL_HISTORY_ACTION_DB IN ('ISSUE')
                    AND (m.INVENTORY_QTY - IFSAPP.INVENTORY_TRANSACTION_HIST_API.Get_Qty_Reversed(m.TRANSACTION_ID)) <> '0'
                    AND m.PART_NO = :part_no
                    AND m.ORDER_REF4 = :line_no
                ORDER BY m.TIME_STAMP DESC
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_no', $orderNo);
            $stmt->bindParam(':release_no', $releaseNo);
            $stmt->bindParam(':sequence_no', $sequenceNo);
            $stmt->bindParam(':contract', $structureContract);
            $stmt->bindParam(':part_no', $partNo);
            $stmt->bindParam(':line_no', $lineNo);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \Exception('Error fetching Pno Component History: ' . $e->getMessage());
        }
    }

    public function getLineItemNo ( string $order_no, string $release_no, string $sequence_no, string $contract, string $part_no)
     {

        try { 
            $sql = '
                SELECT
                    m.LINE_ITEM_NO,
                    IFSAPP.Inventory_Part_API.Get_Description(m.CONTRACT, m.PART_NO) AS description
                FROM IFSAPP.SHOP_MATERIAL_ALLOC_UIV m
                WHERE m.ORDER_NO     = :order_no
                AND m.RELEASE_NO   = :release_no
                AND m.SEQUENCE_NO  = :sequence_no
                AND m.CONTRACT     = :contract
                AND m.PART_NO      = :part_no
                GROUP BY 
                    m.LINE_ITEM_NO,
                    m.PART_NO,
                    IFSAPP.Inventory_Part_API.Get_Description(m.CONTRACT, m.PART_NO)
                ORDER BY m.LINE_ITEM_NO ASC
            ';


            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_no', $order_no);
            $stmt->bindParam(':release_no', $release_no);
            $stmt->bindParam(':sequence_no', $sequence_no);
            $stmt->bindParam(':contract', $contract);
            $stmt->bindParam(':part_no', $part_no);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching parts: ' . $e->getMessage());
        }
    }

    public function getDataValues (string $order_no, string $release_no, string $sequence_no, string $line_no, string $lot, string $partNo, string $contract)
    {
        try {
            $sql = '
                SELECT
                    TO_CHAR(EXPIRATION_DATE, \'YYYY-mm-dd\') AS EXPIRATION_DATE,
                    LOCATION_NO,
                    QTY_ONHAND,
                    SERIAL_NO,
                    ENG_CHG_LEVEL,
                    WAIV_DEV_REJ_NO,
                    ACTIVITY_SEQ,
                    CATCH_QTY_ONHAND,
                    RESERVED_INPUT_QTY,
                    RESERVED_INPUT_UOM,
                    RESERVED_INPUT_VAR_VALUES,
                    HANDLING_UNIT_ID,
                    IFSAPP.Part_Catalog_API.Get_Rcpt_Issue_Serial_Track_Db(PART_NO) AS PART_TRACKING_SESSION_ID
                FROM IFSAPP.SINGLE_MANUAL_ISSUE_SO
                WHERE order_no    = :order_no
                AND release_no  = :release_no
                AND sequence_no = :sequence_no
                AND line_item_no = :line_no
                AND LOT_BATCH_NO = :lot_batch_no
                AND PART_NO      = :part_no
                AND CONTRACT     = :contract
                AND QTY_ONHAND > 0
            ';

            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_no', $order_no);
            $stmt->bindParam(':release_no', $release_no);
            $stmt->bindParam(':sequence_no', $sequence_no);
            $stmt->bindParam(':line_no', $line_no);
            $stmt->bindParam(':lot_batch_no', $lot);
            $stmt->bindParam(':part_no', $partNo);
            $stmt->bindParam(':contract', $contract);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            throw new \Exception('Error fetching Data Values: ' . $e->getMessage());
        }
    }

    public function manualIssue(string $order_no, string $release_no, string $sequence_no, string $line_no, string $contract, string $partNo, string $location_no, string $lot, string $serial_no, string $eng_chg_level, string $waiv_dev_rej, int $activity_seq, int $handling_unit_id, float $catch_qty_hand, float $quantity, float $reserved_input_qty, ?string $reserved_input_uom, ?string $reserved_input_var_values, ?int $part_tracking_session_id )
    {

        try {
            $manual_issue = "
            DECLARE
                INFO_                          VARCHAR2(32000) := '';
                ORDER_NO_                      VARCHAR2(32000) := :order_no;
                RELEASE_NO_                    VARCHAR2(32000) := :release_no;
                SEQUENCE_NO_                   VARCHAR2(32000) := :sequence_no; 
                LINE_ITEM_NO_                  NUMBER          := :line_item_no;   
                CONTRACT_                      VARCHAR2(32000) := :contract;    
                PART_NO_                       VARCHAR2(32000) := :part_no; 
                LOCATION_NO_                   VARCHAR2(32000) := :location_no;   
                LOT_BATCH_NO_                  VARCHAR2(32000) := :lot_batch_no;
                SERIAL_NO_                     VARCHAR2(32000) := :serial_no; 
                ENG_CHG_LEVEL_                 VARCHAR2(32000) := :eng_chg_level;
                WAIV_DEV_REJ_NO_               VARCHAR2(32000) := :waiv_dev_rej_no;
                ACTIVITY_SEQ_                  NUMBER          := :activity_seq;
                HANDLING_UNIT_ID_              NUMBER          := :handling_unit_id;
                CATCH_QTY_                     NUMBER          := :catch_quantity;
                QTY_ISSUED_                    NUMBER          := :quantity_issued;
                INPUT_QTY_                     NUMBER          := :input_quantity; 
                INPUT_UOM_                     VARCHAR2(32000) := :input_uom;
                INPUT_VALUE_                   VARCHAR2(32000) := :input_variable_values;
                PART_TRACKING_SESSION_ID_      NUMBER          :=  NULL;
            BEGIN
                IFSAPP.Shop_Ord_Util_API.Manual_Issue(
                    INFO_,
                    ORDER_NO_, 
                    RELEASE_NO_, 
                    SEQUENCE_NO_, 
                    LINE_ITEM_NO_, 
                    CONTRACT_, 
                    PART_NO_, 
                    LOCATION_NO_, 
                    LOT_BATCH_NO_, 
                    SERIAL_NO_, 
                    ENG_CHG_LEVEL_, 
                    WAIV_DEV_REJ_NO_, 
                    ACTIVITY_SEQ_, 
                    HANDLING_UNIT_ID_,
                    CATCH_QTY_, 
                    QTY_ISSUED_, 
                    INPUT_QTY_, 
                    INPUT_UOM_, 
                    INPUT_VALUE_,
                    PART_TRACKING_SESSION_ID_);

                COMMIT;
            END;
        ";

        $stmt = $this->conn->prepare($manual_issue);

        $stmt->bindParam(':order_no', $order_no);
        $stmt->bindParam(':release_no', $release_no);
        $stmt->bindParam(':sequence_no', $sequence_no);
        $stmt->bindParam(':line_item_no', $line_no);
        $stmt->bindParam(':contract', $contract);
        $stmt->bindParam(':part_no', $partNo);
        $stmt->bindParam(':location_no', $location_no);
        $stmt->bindParam(':lot_batch_no', $lot);
        $stmt->bindParam(':serial_no', $serial_no);
        $stmt->bindParam(':eng_chg_level', $eng_chg_level);
        $stmt->bindParam(':waiv_dev_rej_no', $waiv_dev_rej);
        $stmt->bindParam(':activity_seq', $activity_seq);
        $stmt->bindParam(':handling_unit_id', $handling_unit_id);
        $stmt->bindParam(':catch_quantity', $catch_qty_hand);
        $stmt->bindParam(':quantity_issued', $quantity);
        $stmt->bindParam(':input_quantity', $reserved_input_qty);
        $stmt->bindParam(':input_uom', $reserved_input_uom);
        $stmt->bindParam(':input_variable_values', $reserved_input_var_values);
       
        $stmt->execute();
            return ['message' => 'ok', 'comment' => $partNo];

        } catch (Throwable $e) {
            throw new \Exception('Error manual issue: ' . $e->getMessage());
        }
    }

    public function getScrapCauses(string $search)
    {
        try {

            $search = ltrim($search, '/');

            $scrapCauses = '
                SELECT 
                    REJECT_REASON AS ID, 
                    REJECT_MESSAGE AS DESCRIPTION
                FROM SCRAPPING_CAUSE
                WHERE OBJSTATE = \'Active\'
            ';

            if (!empty($search)) {
                $scrapCauses .= ' AND LOWER(REJECT_MESSAGE) LIKE :search';
            }

            $stmt = $this->conn->prepare($scrapCauses);

            if (!empty($search)) {
                $stmt->bindValue(':search', '%' . strtolower($search) . '%');
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            throw new \Exception('Error fetching scrap causes: ' . $e->getMessage());
        }
    }

    public function reportScrapOperation (string $order_no, string $release_no, string $sequence_no, float $operation_no, float $quantity, string $scrap_cause_id, string $notes) {
        $lsInfo = '';
        $dfReportedScrapQty = null;
        $sFromDL = 'TRUE';
        $sGlobalCompany = '25';
        $sEmployeeId = ''; 
        $sTeamId = '';
        
        try {
        $reportScrapOperation = "
            DECLARE
                p0_ VARCHAR2(32000) := :lsInfo;
                p1_ VARCHAR2(32000) := :order_no;
                p2_ VARCHAR2(32000) := :release_no;
                p3_ VARCHAR2(32000) := :sequence_no;
                p4_ FLOAT := :operation_no;
                p5_ FLOAT := :quantity;
                p6_ FLOAT := :dfReportedScrapQty;
                p7_ VARCHAR2(32000) := :scrap_cause_id;
                p8_ VARCHAR2(32000) := :notes;
                p9_ VARCHAR2(32000) := :sFromDL;
                p10_ VARCHAR2(32000) := :sGlobalCompany;
                p11_ VARCHAR2(32000) := :sEmployeeId;
                p12_ VARCHAR2(32000) := :sTeamId;

            BEGIN
                IFSAPP.SHOP_ORDER_OPERATION_API.Modify_Op_Scrap__
                ( p0_ , 
                p1_ , 
                p2_ , 
                p3_ , 
                p4_ , 
                p5_ , 
                p6_ , 
                p7_ , 
                p8_ , 
                'REPORT',
                p9_ , 
                p10_ , 
                p11_ , 
                p12_ 
                );
                COMMIT;
            END;";

        $stmt = $this->conn->prepare($reportScrapOperation);
        $stmt->bindParam(':lsInfo', $lsInfo);
        $stmt->bindParam(':order_no', $order_no);
        $stmt->bindParam(':release_no', $release_no);
        $stmt->bindParam(':sequence_no', $sequence_no);
        $stmt->bindParam(':operation_no', $operation_no);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':dfReportedScrapQty', $dfReportedScrapQty);
        $stmt->bindParam(':scrap_cause_id', $scrap_cause_id);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':sFromDL', $sFromDL);
        $stmt->bindParam(':sGlobalCompany', $sGlobalCompany);
        $stmt->bindParam(':sEmployeeId', $sEmployeeId);
        $stmt->bindParam(':sTeamId', $sTeamId);
        $stmt->execute();

            return ['message' => 'ok', 'operation_no' => $operation_no];

        } catch (Throwable $e) {
            throw new \Exception('Error reporting scrap operation: ' . $e->getMessage());
        }

    }

    public function reportScrapComponent (string $material_history_id, string $operation_no, float $quantity, string $cause_id, string $notes) {
        $sInfo = '';

        try {
        $reportScrapComponent = "
            DECLARE
                p0_ VARCHAR2(32000) := :sInfo;
                p1_ VARCHAR2(32000) := :material_history_id;
                p2_ FLOAT := :quantity;
                p3_ FLOAT := :operation_no;
                p4_ VARCHAR2(32000) := :cause_id;
                p5_ VARCHAR2(32000) := :notes;

            BEGIN
                IFSAPP.Shop_Material_Alloc_List_API.Scrap_Issued_Component(
                    p0_,
                    p1_,
                    p2_,
                    p3_,
                    p4_,
                    p5_
                );
                COMMIT;
            END;";

        $stmt = $this->conn->prepare($reportScrapComponent);
        $stmt->bindParam(':sInfo', $sInfo);
        $stmt->bindParam(':material_history_id', $material_history_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':operation_no', $operation_no);
        $stmt->bindParam(':cause_id', $cause_id);
        $stmt->bindParam(':notes', $notes);
        $stmt->execute();

            return ['message' => 'ok', 'operation_no' => $operation_no];
        } catch (Throwable $e) {
            throw new \Exception('Error reporting scrap component: ' . $e->getMessage());
        }
    }
        
}
