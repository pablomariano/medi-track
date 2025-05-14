import * as React from "react"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import { Link } from "@inertiajs/react"
import { ChevronLeft, ChevronRight, Home, Calendar, Users, FileText, Settings, PlusCircle } from "lucide-react"
import { cn } from "@/lib/utils"

export function CustomSidebar({ className }: React.HTMLAttributes<HTMLDivElement>) {
  const [isCollapsed, setIsCollapsed] = React.useState(false)

  const navigationItems = [
    { name: "Dashboard", icon: Home, href: "/dashboard" },
    { name: "Appointments", icon: Calendar, href: "/appointments" },
    { name: "Patients", icon: Users, href: "/patients" },
    { name: "Records", icon: FileText, href: "/records" },
    { name: "Settings", icon: Settings, href: "/settings" },
  ]

  return (
    <div
      className={cn(
        "relative flex flex-col border-r bg-background",
        isCollapsed ? "w-16" : "w-64",
        className
      )}
    >
      <div className="flex h-[52px] items-center justify-between px-4 py-2">
        {!isCollapsed && (
          <span className="text-lg font-semibold">MediTrack</span>
        )}
        <Button
          variant="ghost"
          size="icon"
          className="h-6 w-6"
          onClick={() => setIsCollapsed(!isCollapsed)}
        >
          {isCollapsed ? (
            <ChevronRight className="h-4 w-4" />
          ) : (
            <ChevronLeft className="h-4 w-4" />
          )}
        </Button>
      </div>
      <ScrollArea className="flex-1 pt-4">
        <div className="space-y-2 px-2">
          <Button
            asChild
            variant="ghost"
            className={cn(
              "w-full justify-start",
              isCollapsed ? "px-2" : "px-4"
            )}
          >
            <Link href="/new-appointment" className="flex items-center gap-2">
              <PlusCircle className="h-4 w-4" />
              {!isCollapsed && "New Appointment"}
            </Link>
          </Button>
          {navigationItems.map((item) => (
            <Button
              key={item.name}
              asChild
              variant="ghost"
              className={cn(
                "w-full justify-start",
                isCollapsed ? "px-2" : "px-4"
              )}
            >
              <Link href={item.href} className="flex items-center gap-2">
                <item.icon className="h-4 w-4" />
                {!isCollapsed && item.name}
              </Link>
            </Button>
          ))}
        </div>
      </ScrollArea>
      <div className="h-[52px] border-t px-4 py-2">
        <Button
          asChild
          variant="ghost"
          className={cn(
            "w-full justify-start",
            isCollapsed ? "px-2" : "px-4"
          )}
        >
          <Link href="/profile" className="flex items-center gap-2">
            <Users className="h-4 w-4" />
            {!isCollapsed && "Profile"}
          </Link>
        </Button>
      </div>
    </div>
  )
} 