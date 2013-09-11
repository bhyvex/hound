<?php
class ImportdataCommand extends CConsoleCommand {
	public function getHelp() {
		echo "Process the NOC XML data from dmz everynight";
	}
	
	public function run($args) {
		echo "Cron Job Begins \n";
		$xml = simplexml_load_file('https://dmz.nextbridge.org/5ebe2294ecd0e0f08eab7690d2a6ee69.php');
		echo "File downloaded ... \n";
		foreach ($xml->dhcpd->lan->staticmap as $node) {
			$this->_processNode($node, "lan");
		}
		
		echo "==================== Start OPT1 ====================";
		
		foreach ($xml->dhcpd->opt1->staticmap as $node) {
			$this->_processNode($node, "opt1");
		}
		
		echo "==================== Start OPT2 ====================";
		
		foreach ($xml->dhcpd->opt2->staticmap as $node) {
			$this->_processNode($node, "opt2");
		}
		
		echo "==================== Start OPT3 ====================";
		
		foreach ($xml->dhcpd->opt3->staticmap as $node) {
			$this->_processNode($node, "opt3");
		}
		
		echo "==================== Start OPT4 ====================";
		
		foreach ($xml->dhcpd->opt4->staticmap as $node) {
			$this->_processNode($node, "opt4");
		}
		
		echo "==================== Start OPTXXXX ====================";
		
		foreach ($xml->dhcpd->optxxxx->staticmap as $node) {
			$this->_processNode($node, "optxxxx");
		}

	}
	
	private function _processNode($obj, $opt) {
		echo " * Found ".$obj->ipaddr." having MAC ".$obj->mac." and the hostname is ".$obj->hostname." \n";
		
		$data 			= $this->_parseObject($obj);
		$data['opt'] 	= $opt;

		$records 					= Devices::model()->countByHostName($data['hostname']);
		if ($records==0) {
			echo "  - No match for hostname = ".$data['hostname']." found ... ADDING NEW RECORD \n";
			$this->_addRecord($data);
		} elseif ($records==1) {
			echo "  - $records matched for hostname = ".$data['hostname']." found ... UPDATING 1 RECORD \n";
			$employee					= Devices::model()->countByEmpID($data['emp_id']);
			if ($employee==0) {
				echo "   - This Hostname does not have correct EmpID \n";
				$this->_addRecord($data);
			} else {
				$employee				= Devices::model()->find('hostname=:hostname', array(':hostname' => $data['hostname']));
				if ($data['name']!="")
					$employee->name		= $data['name'];
				$employee->mac_address	= $data['mac'];
				$employee->ip_address	= $data['ipaddr'];
				$employee->hostname		= $data['hostname'];
				if ($data['descr']!="")
					$employee->description	= $data['descr'];
				$employee->modified_by	= 1;
				if ($employee->save()) {
					echo "   - Saved EmpID='".$data['emp_id']."' / HOSTNAME='".$data['hostname']."' successfully! \n\n";
				} else {
					echo "   - WARNING: Failed adding ".$data['emp_id']." \n";
					foreach ($employee->getErrors() as $error) {
						echo "    => ".$error[0]."\n";
					}
					echo "\n";
				}
			}
			// free memory
			unset($employee);
		} elseif ($records > 1) {
			echo "  - $records matched for hostname = ".$data['hostname']." found ... UPDATING $records RECORD \n";
			$employee					= Devices::model()->countByHostNameEmpID($data['emp_id'], $data['hostname']);
			if ($employee==0) {
				echo "   - This Hostname does not have correct EmpID \n";
				$this->_addRecord($data);
			} else {
				$employee				= Devices::model()->find('emp_id=:emp_id AND hostname=:hostname',
														array(':emp_id'=>$data['emp_id'], ':hostname'=>$data['hostname']));
				if ($data['name']!="")
					$employee->name		= $data['name'];
				$employee->mac_address	= $data['mac'];
				$employee->ip_address	= $data['ipaddr'];
				if ($data['descr']!="")
					$employee->description	= $data['descr'];
				$employee->modified_by	= 1;
				if ($employee->save()) {
					echo "   - Saved EmpID='".$data['emp_id']."' / HOSTNAME='".$data['hostname']."' successfully! \n\n";
				} else {
					echo "   - WARNING: Failed adding ".$data['emp_id']." \n";
					foreach ($employee->getErrors() as $error) {
						echo "    => ".$error[0]."\n";
					}
					echo "\n";
				}				
			}
		} else {
			//$employee_records 		= Devices::model()->countByHostName($data['emp_id']);
			echo " WARNING! WARNING! WARNING! WARNING! WARNING! ";
			echo "  - RECORDS FOUND ARE '$records', loggically incorrect!! \n";
			print_r($data);
		}

		
	}
	
	private function _parseObject($obj) {
		$descr 		= trim($obj->descr);
		$first_dash	= strpos($descr, "-");
		
		// get employee ID
		$emp_id 		= trim(substr($descr,0,$first_dash));
		// if employee ID is NOT 0; then make sure its 5 digits
		if (is_numeric($emp_id)!=1 || empty($emp_id)) {
			$emp_id = '';
		} else {
			$emp_id	= str_pad(trim(substr($descr,0,$first_dash)), 5, "0", STR_PAD_LEFT);
			//$employeeDataAPIresponse = $this->_callNextHRM($emp_id);
			//$employeeDataArray = json_decode($employeeDataAPIresponse->getSitting->response);
		}
		
		// PATCH1: if we dont have a "-" in the string, then dont truncate
		if ($first_dash<>0) {
			$newpos = $first_dash+1;
		} else {
			$newpos = $first_dash;	
		}
		
		// trim the empDescr
		$empDesc = trim(substr($descr,$newpos,strlen($descr)));
		return array(
						'mac'		=> (string)$obj->mac,
						'ipaddr'	=> (string)$obj->ipaddr,
						'hostname'	=> (string)$obj->hostname,
						'descr'		=> $descr,
						'emp_id'	=> $emp_id,
						'name'		=> $empDesc,
					);
	}
	
	private function _addRecord($data) {
		echo "   This is _addRecord(); \n";

		$employee 				= new Devices;
		$employee->emp_id		= $data['emp_id'];
		$employee->name			= ($data['name'] != "") ? $data['name'] : "";
		$employee->mac_address	= $data['mac'];
		$employee->ip_address	= $data['ipaddr'];
		$employee->hostname		= $data['hostname'];
		$employee->description	= ($data['descr'] != "") ? $data['descr'] : "";
		$employee->created		= new CDbExpression('NOW()');
		$employee->created_by	= 1;
		$employee->modified_by	= 1;
		$employee->opt			= ($data['opt'] != "") ? $data['opt'] : "opt";
		$employee->hall			= "N/A";
		$employee->line_manager	= "N/A";
		$employee->location		= "N/A";

		if ($employee->save()) {
			echo "   - Record ".$data['hostname']." added! \n";
			return true;
		} else {
			echo "   - WARNING: Failed adding ".$data['hostname']."\n";
			foreach ($employee->getErrors() as $error) {
				echo "    => ".$error[0]."\n";	
			}
			echo "\n";
			return false;
		}
	}
	
	private function _callNextHRM($id) {
		return simplexml_load_file('http://nexthrm.vteamslabs.com/web-service/?auth=7eedf192b67b0b15dee3491b286babc9&method=getSitting&userName=noc@nexthrm.com&empId='.$id.'&empName=s');
	}
}
?>