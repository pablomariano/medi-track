import { Button } from "@/components/ui/button"
import { Heart, LogIn } from "lucide-react"
import Link from "next/link"

export function Header() {
  return (
    <header className="absolute top-0 left-0 right-0 z-30 bg-transparent">
      <div className="max-w-7xl mx-auto px-4 py-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <Heart className="h-8 w-8 text-green-400 mr-2" />
            <span className="text-2xl font-bold text-white">
              Medi<span className="text-green-400">-Track</span>
            </span>
          </div>

          <nav className="hidden md:flex items-center space-x-8">
            <Link href="#beneficios" className="text-white hover:text-green-400 transition-colors">
              Beneficios
            </Link>
            <Link href="#como-funciona" className="text-white hover:text-green-400 transition-colors">
              Cómo Funciona
            </Link>
            <Link href="#soporte" className="text-white hover:text-green-400 transition-colors">
              Ayuda
            </Link>
          </nav>

          <Button
            variant="outline"
            className="border-white text-white hover:bg-white hover:text-blue-900 transition-all duration-300 bg-transparent"
            asChild
          >
            <Link href="/login">
              <LogIn className="mr-2 h-4 w-4" />
              Iniciar Sesión
            </Link>
          </Button>
        </div>
      </div>
    </header>
  )
}
