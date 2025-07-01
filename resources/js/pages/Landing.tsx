import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Clock, BarChart3, Users, Bell, FileText, Shield, Play, ArrowRight, Heart, LogIn } from "lucide-react"
import { Link } from "@inertiajs/react"

export default function Landing() {
  const benefits = [
    {
      icon: Clock,
      title: "Nunca Olvides Tus Medicinas",
      description:
        "Recordatorios inteligentes que se adaptan a tu horario. Perfecto para personas mayores y tratamientos complejos.",
    },
    {
      icon: BarChart3,
      title: "Ve Tu Progreso Fácilmente",
      description: "Gráficos simples que muestran cómo vas con tu tratamiento. Información clara para ti y tu familia.",
    },
    {
      icon: Users,
      title: "Mantén a Tu Familia Tranquila",
      description: "Tus seres queridos pueden ver que estás tomando tus medicinas. Ideal para cuidadores y familiares.",
    },
    {
      icon: Bell,
      title: "Recordatorios Suaves",
      description: "Alertas personalizadas que no molestan. Se adaptan a tu rutina y preferencias personales.",
    },
    {
      icon: FileText,
      title: "Reportes para Tu Doctor",
      description: "Lleva un registro completo para mostrar en tus citas médicas. Tu doctor verá tu compromiso.",
    },
    {
      icon: Shield,
      title: "Método Científico Confiable",
      description: "Usamos escalas médicas validadas para medir tu adherencia de forma precisa y confiable.",
    },
  ]

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="absolute top-0 left-0 right-0 z-30 bg-card border-b-4 border-black">
        <div className="max-w-7xl mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <Heart className="h-8 w-8 text-primary mr-2" />
              <span className="text-2xl font-bold text-foreground font-sans">
                Medi<span className="text-primary">-Track</span>
              </span>
            </div>

            <nav className="hidden md:flex items-center space-x-8">
              <a href="#beneficios" className="text-foreground hover:text-primary transition-colors font-medium">
                Beneficios
              </a>
              <a href="#como-funciona" className="text-foreground hover:text-primary transition-colors font-medium">
                Cómo Funciona
              </a>
              <a href="#soporte" className="text-foreground hover:text-primary transition-colors font-medium">
                Ayuda
              </a>
            </nav>

            <Button
              variant="outline"
              className="border-2 border-black bg-background text-foreground hover:bg-primary hover:text-primary-foreground transition-all duration-300"
              asChild
            >
              <Link href={route('login')}>
                <LogIn className="mr-2 h-4 w-4" />
                Iniciar Sesión
              </Link>
            </Button>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-secondary border-b-4 border-black">
        {/* Hero Content */}
        <div className="relative z-20 text-center text-foreground px-4 max-w-4xl mx-auto pt-20">
          <div className="flex items-center justify-center mb-8">
            <div className="bg-primary p-4 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
              <Heart className="h-16 w-16 text-primary-foreground" />
            </div>
          </div>
          
          <h1 className="text-6xl md:text-8xl font-bold mb-6 font-sans">
            Medi<span className="text-primary">-Track</span>
          </h1>

          <div className="bg-card border-4 border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] mb-8">
            <h2 className="text-3xl md:text-5xl font-bold mb-6 leading-tight">Cuida Tu Salud Sin Complicaciones</h2>
            <p className="text-xl md:text-2xl mb-8 max-w-3xl mx-auto leading-relaxed text-muted-foreground">
              La app que te ayuda a seguir tu tratamiento médico de forma fácil. Perfecta para pacientes, adultos mayores
              y cuidadores que buscan tranquilidad.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <Button
              size="lg"
              className="bg-primary hover:bg-primary/90 text-primary-foreground px-8 py-4 text-lg font-bold border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-all duration-300"
              asChild
            >
              <Link href={route('register')}>
                Empezar Gratis
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>

            <Button
              variant="outline"
              size="lg"
              className="border-4 border-black bg-card text-foreground hover:bg-accent hover:text-accent-foreground px-8 py-4 text-lg font-bold shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-all duration-300"
            >
              <Play className="mr-2 h-5 w-5" />
              Ver Cómo Funciona
            </Button>
          </div>

          <div className="mt-12 bg-accent/20 border-2 border-black p-4 inline-block">
            <p className="text-sm font-medium">✨ Más de 15,000 personas y familias nos eligen cada día</p>
          </div>
        </div>
      </section>

      {/* Benefits Section */}
      <section id="beneficios" className="py-20 px-4 bg-background">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <div className="inline-block bg-primary p-4 border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] mb-8">
              <h2 className="text-4xl md:text-5xl font-bold text-primary-foreground font-sans">Diseñado Para Personas Como Tú</h2>
            </div>
            <div className="bg-card border-2 border-black p-6 max-w-4xl mx-auto">
              <p className="text-xl text-muted-foreground leading-relaxed">
                Ya seas un paciente que quiere cuidar mejor su salud, o un cuidador que busca tranquilidad, Medi-Track te
                acompaña en cada paso de tu tratamiento.
              </p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {benefits.map((benefit, index) => (
              <Card
                key={index}
                className="group border-4 border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] transition-all duration-300 bg-card hover:bg-secondary/50"
              >
                <CardHeader className="text-center pb-4">
                  <div className="mx-auto mb-4 p-4 bg-accent border-4 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] w-20 h-20 flex items-center justify-center group-hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-all duration-300">
                    <benefit.icon className="h-10 w-10 text-accent-foreground" />
                  </div>
                  <CardTitle className="text-xl font-bold text-foreground group-hover:text-primary transition-colors font-sans">
                    {benefit.title}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="text-muted-foreground text-center leading-relaxed font-medium">
                    {benefit.description}
                  </CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-primary border-y-4 border-black">
        <div className="max-w-4xl mx-auto text-center px-4">
          <div className="bg-primary-foreground border-4 border-black p-8 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] mb-8">
            <h2 className="text-4xl md:text-5xl font-bold text-primary mb-6 font-sans">Dale Tranquilidad a Tu Familia Hoy</h2>
            <p className="text-xl text-foreground mb-8 max-w-2xl mx-auto leading-relaxed">
              Miles de personas ya cuidan mejor su salud con Medi-Track. Únete a ellos y vive con la confianza de estar
              siempre al día con tu tratamiento.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <Button
              size="lg"
              className="bg-secondary hover:bg-secondary/90 text-foreground px-10 py-4 text-lg font-bold border-4 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-all duration-300"
              asChild
            >
              <Link href={route('register')}>
                Comenzar Ahora - Es Gratis
                <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>

            <Button
              variant="outline"
              size="lg"
              className="border-4 border-black bg-primary-foreground text-foreground hover:bg-accent hover:text-accent-foreground px-8 py-4 text-lg font-bold shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] transition-all duration-300"
            >
              Necesito Ayuda
            </Button>
          </div>

          <div className="mt-8 bg-black text-primary-foreground p-4 border-4 border-black inline-block shadow-[4px_4px_0px_0px_rgba(255,255,255,1)]">
            <p className="text-sm font-bold">✓ Siempre gratis • ✓ Muy fácil de usar • ✓ Soporte en español 24/7</p>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-card border-t-4 border-black py-12">
        <div className="max-w-6xl mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div className="col-span-1 md:col-span-2">
              <div className="flex items-center mb-6">
                <div className="bg-primary p-2 border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mr-3">
                  <Heart className="h-6 w-6 text-primary-foreground" />
                </div>
                <span className="text-2xl font-bold text-foreground font-sans">
                  Medi<span className="text-primary">-Track</span>
                </span>
              </div>
              <div className="bg-secondary/30 border-2 border-black p-4 mb-4 max-w-md">
                <p className="text-foreground">
                  La aplicación que cuida tu salud y tranquiliza a tu familia. Diseñada especialmente para pacientes y
                  cuidadores.
                </p>
              </div>
              <div className="bg-black text-primary-foreground p-2 border-2 border-black inline-block">
                <p className="text-sm font-bold">© 2024 Medi-Track. Todos los derechos reservados.</p>
              </div>
            </div>

            <div>
              <div className="bg-accent p-3 border-2 border-black mb-4 inline-block">
                <h3 className="font-bold text-accent-foreground">Para Ti</h3>
              </div>
              <ul className="space-y-3">
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Cómo Empezar
                  </a>
                </li>
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Guía de Uso
                  </a>
                </li>
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Para Cuidadores
                  </a>
                </li>
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Testimonios
                  </a>
                </li>
              </ul>
            </div>

            <div>
              <div className="bg-secondary p-3 border-2 border-black mb-4 inline-block">
                <h3 className="font-bold text-foreground">Soporte</h3>
              </div>
              <ul className="space-y-3">
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Centro de Ayuda
                  </a>
                </li>
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Contacto
                  </a>
                </li>
                <li>
                  <a href="#" className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    WhatsApp
                  </a>
                </li>
                <li>
                  <Link href={route('login')} className="text-muted-foreground hover:text-primary transition-colors font-medium border-b-2 border-transparent hover:border-primary pb-1">
                    Iniciar Sesión
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
} 