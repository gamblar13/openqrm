#cs ----------------------------------------------------------------------------

 AutoIt Version: 3.3.6.1
 Author:         myName

 Script Function:
	Template AutoIt script.

#ce ----------------------------------------------------------------------------

; Script Start - Add your code below here
;


Func Random_Password($MAXLENGTH)
    Local $PASSWORD = ''
    For $i = 1 To $MAXLENGTH
        $PASSWORD = $PASSWORD & Chr(Random(33, 126))
    Next
    Return $PASSWORD
EndFunc

$OPENQRM_SERVER=$CmdLine[1]
$RESOURCE_ID=$CmdLine[2]

; make sure user root exists
$ROOT_PASSWORD=Random_Password(15)
RunWait(@COMSPEC & " /c " & "net user root /DELETE")
RunWait(@COMSPEC & " /c " & "net user root $ROOT_PASSWORD /add")
RunWait(@COMSPEC & " /c " & "net localgroup Administrators root /add")
RunWait(@COMSPEC & " /c " & "net localgroup Administratoren root /add")


Run("openQRM-Client-4.8.0-setup.exe")

WinWaitActive("openQRM-Client-4.7.2 Setup", "&Next")
Send("!n")
WinWaitActive("openQRM-Client-4.7.2 Setup", "&Agree")
Send("!a")
Send("{ENTER}")
WinWaitActive("openQRM-Client-4.7.2 Setup", "&Install")
Send("!i")
WinWaitActive("openQRM-Client-4.7.2 Setup", "Copssh installation. Please install in C:\openQRM-Client\ICW")
Send("{ENTER}")

; copssh install
WinWaitActive("Copssh 3.1.4 Setup", "&Next")
Send("!n")
WinWaitActive("Copssh 3.1.4 Setup", "&Agree")
Send("!a")
WinWaitActive("Copssh 3.1.4 Setup", "Choose the folder in which to install Copssh 3.1.4.")
Send("C:\openQRM-Client\ICW\")
Send("!n")
; setup service account
WinWaitActive("Copssh 3.1.4 Setup", "Service Account")
Send("!i")
WinWaitActive("ICW OpenSSHServer 2.0.4 Setup", "OK")
Send("{ENTER}")
; closing installation
WinActivate("Copssh 3.1.4 Setup")

WinWaitActive("Copssh 3.1.4 Setup", "Setup was completed successfully.")
Send("!c")




; activate user root
WinActivate("[TITLE:openQRM-Client-4.7.2 Setup; CLASS:Button; INSTANCE:1]")

WinWaitActive("openQRM-Client-4.7.2 Setup", "OK")
Send("{ENTER}")
WinWaitActive("copSSH User Activation Wizard", "User name")
Send("root")
Send("!n")

Send("openqrm")
Send("{TAB}")
Send("openqrm")
Send("{TAB}")
Send("{TAB}")
Send("{ENTER}")

WinWaitActive("UserActivationWizard 2.0 Setup", "OK")
Send("{ENTER}")




; set openQRM ip
WinActivate("[TITLE:openQRM-Client-4.7.2 Setup; CLASS:Button; INSTANCE:1]")

WinWaitActive("openQRM-Client-4.7.2 Setup", "OK")
Send("{ENTER}")

WinActivate("openQRM-Client-4.7.2 Setup", "0.0.0.0")

WinWaitActive("openQRM-Client-4.7.2 Setup", "0.0.0.0")
Send("{TAB}")
Send("{TAB}")
Send($OPENQRM_SERVER)
Send("!n")

; set resource id
WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "OK")
Send("{ENTER}")

WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "Installation Complete")
Send("{TAB}")
Send("{TAB}")
Send($RESOURCE_ID)
Send("!n")

; get parameter
WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "OK")
Send("{ENTER}")
; install monitoring service
WinActivate("openQRM-Client-4.7.2 Setup", "Adding openQRM Monitoring Service")

WinWaitActive("openQRM-Client-4.7.2 Setup", "Adding openQRM Monitoring Service")
Send("{ENTER}")

; final install screen
WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "&Finish")
Send("{ENTER}")



