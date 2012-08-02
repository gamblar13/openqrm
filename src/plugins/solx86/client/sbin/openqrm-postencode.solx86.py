#!/usr/bin/python
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

import sys, urllib, base64
input_filename = sys.argv[1]
postwad_filename = input_filename + ".post"
datawad = base64.encodestring(file(input_filename, "rb").read())
postwad = urllib.urlencode({"filedata":datawad, "filename":input_filename})
file(postwad_filename, "wb").write(postwad)
print postwad_filename
