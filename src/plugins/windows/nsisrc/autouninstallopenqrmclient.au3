#cs ----------------------------------------------------------------------------

 AutoIt Version: 3.3.6.1
 Author:         myName

 Script Function:
	Template AutoIt script.

#ce ----------------------------------------------------------------------------

; Script Start - Add your code below here
;

;MsgBox(0, "Tutorial", "openQRM Client autoinst!")

If FileExists("C:\openQRM-Client\Uninstall.exe") Then
	Run("C:\openQRM-Client\Uninstall.exe")

	; openqrm client uninstall
	WinWaitActive("openQRM-Client-4.7.2 Uninstall", "&Next")
	Send("!n")
	WinWaitActive("openQRM-Client-4.7.2 Uninstall", "&Uninstall")
	Send("!u")

	; copssh uninstall
	WinActivate("openQRM-Client-4.7.2 Setup", "Copssh Uninstallation")
	
	WinWaitActive("openQRM-Client-4.7.2 Uninstall", "Copssh Uninstallation")
	Send("{ENTER}")

	WinActivate("Copssh 3.1.4 Uninstall", "Uninstall Copssh 3.1.4")
	
	WinWaitActive("Copssh 3.1.4 Uninstall", "Uninstall Copssh 3.1.4")
	Send("!u")

	WinActivate("Copssh 3.1.4 Uninstall", "Uninstallation Complete")
	
	WinWaitActive("Copssh 3.1.4 Uninstall", "Uninstallation Complete")
	Send("{ENTER}")

	; final install screen
	WinActivate("openQRM-Client-4.7.2 Uninstall")
	
	WinWaitActive("openQRM-Client-4.7.2 Uninstall", "&Finish")
	Send("{ENTER}")
EndIf


If FileExists("C:\openQRM-Client\ICW\uninstall_Copssh.exe") Then
	Run("C:\openQRM-Client\ICW\uninstall_Copssh.exe")

	WinActivate("Copssh 3.1.4 Uninstall", "Uninstall Copssh 3.1.4")
	
	WinWaitActive("Copssh 3.1.4 Uninstall", "Uninstall Copssh 3.1.4")
	Send("!u")

	WinActivate("Copssh 3.1.4 Uninstall", "Uninstallation Complete")
	
	WinWaitActive("Copssh 3.1.4 Uninstall", "Uninstallation Complete")
	Send("{ENTER}")
EndIf

RunWait(@COMSPEC & " /c " & "net user SvcCOPSSH /DELETE")

