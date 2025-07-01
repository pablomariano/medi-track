import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Clock, BarChart3, Users, Bell, FileText, Shield, Play, ArrowRight, Heart } from "lucide-react"
import Link from "next/link"
import { Header } from "./components/header"

export default function MediTrackLanding() {
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
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-green-50">
      <Header />

      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        {/* Background */}
        <div className="absolute inset-0 bg-gradient-to-r from-blue-900/90 to-green-800/90 z-10"></div>
        <div className="absolute inset-0 bg-[url('/placeholder.svg?height=1080&width=1920')] bg-cover bg-center opacity-30"></div>

        {/* Hero Content */}
        <div className="relative z-20 text-center text-white px-4 max-w-4xl mx-auto">
          <div className="flex items-center justify-center mb-6">
            <Heart className="h-12 w-12 text-green-400 mr-3" />
            <h1 className="text-5xl md:text-7xl font-bold">
              Medi<span className="text-green-400">-Track</span>
            </h1>
          </div>

          <h2 className="text-2xl md:text-4xl font-light mb-6 leading-tight">Cuida Tu Salud Sin Complicaciones</h2>

          <p className="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto leading-relaxed">
            La app que te ayuda a seguir tu tratamiento médico de forma fácil. Perfecta para pacientes, adultos mayores
            y cuidadores que buscan tranquilidad.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <Button
              size="lg"
              className="bg-green-600 hover:bg-green-700 text-white px-8 py-4 text-lg font-semibold rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
            >
              Empezar Gratis
              <ArrowRight className="ml-2 h-5 w-5" />
            </Button>

            <Button
              variant="outline"
              size="lg"
              className="border-white text-white hover:bg-white hover:text-blue-900 px-8 py-4 text-lg font-semibold rounded-full transition-all duration-300 bg-transparent"
            >
              <Play className="mr-2 h-5 w-5" />
              Ver Cómo Funciona
            </Button>
          </div>

          <div className="mt-12 text-sm opacity-75">
            <p>Más de 15,000 personas y familias nos eligen cada día</p>
          </div>
        </div>
      </section>

      {/* Benefits Section */}
      <section id="beneficios" className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Diseñado Para Personas Como Tú</h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
              Ya seas un paciente que quiere cuidar mejor su salud, o un cuidador que busca tranquilidad, Medi-Track te
              acompaña en cada paso de tu tratamiento.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {benefits.map((benefit, index) => (
              <Card
                key={index}
                className="group hover:shadow-xl transition-all duration-300 border-0 shadow-lg bg-white/80 backdrop-blur-sm hover:bg-white hover:scale-105"
              >
                <CardHeader className="text-center pb-4">
                  <div className="mx-auto mb-4 p-4 bg-gradient-to-br from-blue-500 to-green-500 rounded-full w-16 h-16 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <benefit.icon className="h-8 w-8 text-white" />
                  </div>
                  <CardTitle className="text-xl font-bold text-gray-900 group-hover:text-blue-700 transition-colors">
                    {benefit.title}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="text-gray-600 text-center leading-relaxed">
                    {benefit.description}
                  </CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-blue-600 to-green-600">
        <div className="max-w-4xl mx-auto text-center px-4">
          <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">Dale Tranquilidad a Tu Familia Hoy</h2>
          <p className="text-xl text-blue-100 mb-8 max-w-2xl mx-auto leading-relaxed">
            Miles de personas ya cuidan mejor su salud con Medi-Track. Únete a ellos y vive con la confianza de estar
            siempre al día con tu tratamiento.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <Button
              size="lg"
              className="bg-white text-blue-600 hover:bg-blue-50 px-10 py-4 text-lg font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
            >
              Comenzar Ahora - Es Gratis
              <ArrowRight className="ml-2 h-5 w-5" />
            </Button>

            <Button
              variant="outline"
              size="lg"
              className="border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 text-lg font-semibold rounded-full transition-all duration-300 bg-transparent"
            >
              Necesito Ayuda
            </Button>
          </div>

          <div className="mt-8 text-blue-100 text-sm">
            <p>✓ Siempre gratis • ✓ Muy fácil de usar • ✓ Soporte en español 24/7</p>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-12">
        <div className="max-w-6xl mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div className="col-span-1 md:col-span-2">
              <div className="flex items-center mb-4">
                <Heart className="h-8 w-8 text-green-400 mr-2" />
                <span className="text-2xl font-bold">
                  Medi<span className="text-green-400">-Track</span>
                </span>
              </div>
              <p className="text-gray-400 mb-4 max-w-md">
                La aplicación que cuida tu salud y tranquiliza a tu familia. Diseñada especialmente para pacientes y
                cuidadores.
              </p>
              <p className="text-sm text-gray-500">© 2024 Medi-Track. Todos los derechos reservados.</p>
            </div>

            <div>
              <h3 className="font-semibold mb-4">Para Ti</h3>
              <ul className="space-y-2 text-gray-400">
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Cómo Empezar
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Guía de Uso
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Para Cuidadores
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Testimonios
                  </Link>
                </li>
              </ul>
            </div>

            <div>
              <h3 className="font-semibold mb-4">Ayuda</h3>
              <ul className="space-y-2 text-gray-400">
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Preguntas Frecuentes
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Soporte 24/7
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Contacto
                  </Link>
                </li>
                <li>
                  <Link href="#" className="hover:text-white transition-colors">
                    Tutoriales
                  </Link>
                </li>
              </ul>
            </div>
          </div>

          <div className="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>Tu salud es lo más importante. Nosotros te ayudamos a cuidarla.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}
