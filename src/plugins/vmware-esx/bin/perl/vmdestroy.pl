#!/usr/bin/perl -w
#
# this is a perl script to destroy a VM
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
      help => "The name of the VM to destroy",
      required => 0,
      default => "none",
   },
);

Opts::add_options(%opts);
Opts::parse();
# Opts::validate(\&validate);

Util::connect();
destroy_vms();
Util::disconnect();

sub destroy_vms {
	my $vmname = Opts::get_option('vmname');
	my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', filter => { 'config.name' => $vmname });
	foreach (@$vm_views) {
		$_->Destroy ;
	}
}

