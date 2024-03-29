#!/usr/bin/perl -w
#
# this is a perl script to destroy a VM
#
# openQRM Enterprise developed by openQRM Enterprise GmbH.
#
# All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.
#
# This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
# The latest version of this license can be found here: src/doc/LICENSE.txt
#
# By using this software, you acknowledge having read this license and agree to be bound thereby.
#
#           http://openqrm-enterprise.com
#
# Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
#


use strict;
use warnings;

use FindBin;
use lib "/usr/lib/vmware-vcli/apps/";

use VMware::VIRuntime;
use XML::LibXML;
use AppUtil::XMLInputUtil;
use AppUtil::HostUtil;


$Util::script_version = "1.0";

my %opts = (
	vmname => {
		type => "=s",
		help => "The VM name",
		required => 1,
	},
);

Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);

my $vmname = Opts::get_option('vmname');

Util::connect();

my $vm_view = Vim::find_entity_view(
	view_type => 'VirtualMachine',
	filter => {
		'name' =>  $vmname
	}
);

# state
my $vm_state;
if ($vm_view->runtime->powerState->val eq 'poweredOn') {
	$vm_state = "active";
} else {
	$vm_state = "inactive";
}
# mem
my $memsize = $vm_view->config->hardware->memoryMB;
# cpu
my $cpus = $vm_view->config->hardware->numCPU;

# devices
my $devices = $vm_view->config->hardware->device;
my $nic_management = '';
my $nic_additional = '';
my $nic_type = '';
my $devloop = 0;
foreach my $device (@$devices){

# debug
# print $device->deviceInfo->label;

	if($device->isa("VirtualEthernetCard")) {
		if ( $device->isa('VirtualE1000'))  {
			$nic_type = 'VirtualE1000';
		} elsif ($device->isa('VirtualPCNet32')) {
			$nic_type = 'VirtualPCNet32';
		} elsif ($device->isa('VirtualVmxnet')) {
			$nic_type = 'VirtualVmxnet';
		}

		if ($devloop == 0) {
			$nic_management = $device->macAddress . ",". $nic_type;
		} elsif ($devloop == 1) {
			$nic_additional = $device->macAddress . ",". $nic_type;
		} else {
			$nic_additional = $nic_additional . "/" . $device->macAddress . ",". $nic_type;
		}
		$devloop++;
	}

}
print $vmname."@".$vm_state."@".$cpus."@".$memsize."@".$nic_management."@".$nic_additional."";

Util::disconnect();




