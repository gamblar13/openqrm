<pre>
# openQRM requires the following environment variables to be set in the rc-config file :
# EC2_URL
# EC2_ACCESS_KEY
# EC2_SECRET_KEY
# EC2_CERT
# EC2_PRIVATE_KEY
# S3_URL
# EUCALYPTUS_CERT

# Below an example cloud rc-config file (here for UEC)

EUCA_KEY_DIR=$(dirname $(readlink -f ${BASH_SOURCE}))
export S3_URL=http://192.168.88.101:8773/services/Walrus
export EC2_URL=http://192.168.88.101:8773/services/Eucalyptus
export EC2_PRIVATE_KEY=${EUCA_KEY_DIR}/euca2-admin-a9956173-pk.pem
export EC2_CERT=${EUCA_KEY_DIR}/euca2-admin-a9956173-cert.pem
export EC2_JVM_ARGS=-Djavax.net.ssl.trustStore=${EUCA_KEY_DIR}/jssecacerts
export EUCALYPTUS_CERT=${EUCA_KEY_DIR}/cloud-cert.pem
export EC2_ACCESS_KEY='WKy3rMzOWPouVOxK1p3Ar1C2uRBwa2FBXnCw'
export EC2_SECRET_KEY='JCYdK17HMVRn8JM7TG2BfwXRTE6RPr6h9OD9w'
# This is a bogus value; Eucalyptus does not need this but client tools do.
export EC2_USER_ID='85282682761863927252080438378529030154'
alias ec2-bundle-image="ec2-bundle-image --cert ${EC2_CERT} --privatekey ${EC2_PRIVATE_KEY} --user 85282682761863927252080438378529030154 --ec2cert ${EUCALYPTUS_CERT}"
alias ec2-upload-bundle="ec2-upload-bundle -a ${EC2_ACCESS_KEY} -s ${EC2_SECRET_KEY} --url ${S3_URL} --ec2cert ${EUCALYPTUS_CERT}"
</pre>