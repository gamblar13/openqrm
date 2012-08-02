#cs ----------------------------------------------------------------------------

 AutoIt Version: 3.3.6.1
 Author:         myName

 Script Function:
	Template AutoIt script.

#ce ----------------------------------------------------------------------------

; Script Start - Add your code below here
;

;MsgBox(0, "Tutorial", "openQRM Client autoinst!")

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
Send("192.168.88.10")
Send("!n")

; set resource id
WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "OK")
Send("{ENTER}")

WinActivate("openQRM-Client-4.7.2 Setup")

WinWaitActive("openQRM-Client-4.7.2 Setup", "Installation Complete")
Send("{TAB}")
Send("{TAB}")
Send("10")
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



