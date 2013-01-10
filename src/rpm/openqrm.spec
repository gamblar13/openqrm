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
Name: OPENQRM_PACKAGE_NAME
Summary: OPENQRM_PACKAGE_NAME
Version: OPENQRM_PACKAGE_VERSION
Release: OPENQRM_PACKAGE_DISTRIBUTION
License: GPL
Group: Networking/Admin
AutoReqProv: no
Source: OPENQRM_PACKAGE_NAME-OPENQRM_PACKAGE_VERSION.tgz
Prefix: /
BuildRoot: /tmp/openqrm-packaging/OPENQRM_PACKAGE_NAME
Requires : OPENQRM_PACKAGE_DEPENDENCIES
BuildRequires: OPENQRM_SERVER_BUILD_REQUIREMENTS
%description
openQRM is the next generation data-center management platform.

%files
%defattr(-,root,root)
/usr/share/openqrm/*

%prep
%setup

%build
make

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/usr/share
make install DESTINATION_DIR=$RPM_BUILD_ROOT
OPENQRM_BUILD_POSTINSTALL

%pre
if [ -x "OPENQRM_PACKAGE_PREINSTALL_SCRIPT" ]; then OPENQRM_PACKAGE_PREINSTALL; fi

%post
OPENQRM_PACKAGE_POSTINSTALL

%preun
OPENQRM_PACKAGE_PREREMOVE

%clean
rm -rf $RPM_BUILD_ROOT
make clean
