# Exam Fullscreen Without User Interaction (Kiosk Mode)

For browser security reasons, a normal web page cannot force fullscreen without a user gesture.

To achieve no-examinee-interaction fullscreen behavior, run the exam in browser kiosk mode on exam devices.

## Option 1: One-time launch command

From the project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\launch-exam-kiosk.ps1 -Url "https://your-domain/exam/{vacancy_id}/lobby" -Browser edge -KillExisting
```

You can switch browser with `-Browser chrome`.

## Option 2: Double-click launcher

Use:

```cmd
scripts\launch-exam-kiosk.cmd "https://your-domain/exam/{vacancy_id}/lobby" edge
```

## Notes

- Kiosk mode starts browser in fullscreen immediately.
- Edge command uses `--kiosk` with `--edge-kiosk-type=fullscreen`.
- Close kiosk session with `Alt+F4` (administrator side).
- Run this from a proctor/admin account on each testing machine before examinees start.

## Recommended deployment

- Create a desktop shortcut on each exam PC using the command above.
- Preload the exact exam lobby URL per room/session.
- Disable OS notifications and unrelated startup apps for fewer distractions.
