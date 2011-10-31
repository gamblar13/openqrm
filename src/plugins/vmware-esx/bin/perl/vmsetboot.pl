#!/usr/bin/perl -w
#
# this is a perl script to set the boot order of a VM
#
# This file is part of openQRM.
#
# openQRM is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2
# as published by the Free Software Foundation.
#
# openQRM is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with openQRM.  If not, see <http://www.gnu.org/licenses/>.
#
# Copyright 2011, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
#

use strict;
use warnings;

use lib "/usr/lib/vmware-vcli/apps/";

use VMware::VIRuntime;
use AppUtil::VMUtil;
use Data::Dumper;

my %opts = (
	vmname => {
		type => "=s",
		help => "The name of the VM",
		required => 1,
	},
	bootorder => {
	  type => "=s",
	  help => "one of net, hd or cd",
	  required => 1,
	},
);

Opts::add_options(%opts);
Opts::parse();
Opts::validate();
Util::connect();

my %filterhash = ();
my $vmname = Opts::get_option('vmname');
my $bootorder = Opts::get_option('bootorder');
my $vm_view = Vim::find_entity_view(view_type => 'VirtualMachine', filter => {name => $vmname});
if($vm_view) {
	my $vm_config_spec = VirtualMachineConfigSpec->new(
		name => $vmname,
		extraConfig => [OptionValue->new( key => 'bios.bootDeviceClasses',
		value => $bootorder ),]
	);
    $vm_view->ReconfigVM( spec => $vm_config_spec );
}
Util::disconnect();

