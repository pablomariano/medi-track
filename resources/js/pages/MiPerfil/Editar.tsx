import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { User, Heart, Phone, MapPin, Calendar, ArrowLeft, CheckCircle } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Alert, AlertDescription } from '@/components/ui/alert';

export default function EditarPerfil() {
  const auth = useAuth();
  const user = auth.user;
  
  const [formData, setFormData] = useState({
    nombre: user?.nombre ? String(user.nombre) : '',
    apellido_paterno: user?.apellido_paterno ? String(user.apellido_paterno) : '',
    apellido_materno: user?.apellido_materno ? String(user.apellido_materno) : '',
    telefono: user?.telefono ? String(user.telefono) : '',
    fecha_nacimiento: '',
    direccion: '',
    contacto_emergencia_nombre: '',
    contacto_emergencia_telefono: '',
    contacto_emergencia_relacion: '',
    alergias: '',
    condiciones_medicas: '',
    tipo_sangre: '',
    altura: '',
    peso: '',
  });

  const [processing, setProcessing] = useState(false);

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    
    // Simular envío de datos
    setTimeout(() => {
      setProcessing(false);
      router.visit('/bienvenida');
    }, 1000);
  };

  const getProfileCompleteness = () => {
    const fields = ['nombre', 'apellido_paterno', 'telefono', 'fecha_nacimiento'];
    const completed = fields.filter(field => formData[field as keyof typeof formData] !== '').length;
    return Math.round((completed / fields.length) * 100);
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-4xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center gap-4 mb-4">
            <Link href="/bienvenida">
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver a Bienvenida
              </Button>
            </Link>
            <Badge variant="secondary">Configuración Inicial</Badge>
          </div>
          <h1 className="text-3xl font-bold text-gray-900">
            Completa tu perfil
          </h1>
          <p className="text-gray-600 mt-2">
            Esta información nos ayudará a personalizar tu experiencia y mantenerte seguro
          </p>
        </div>

        {/* Indicador de progreso */}
        <Card className="mb-6">
          <CardContent className="pt-6">
            <div className="flex items-center gap-4">
              <div className="flex-1">
                <div className="flex justify-between text-sm mb-2">
                  <span>Completitud del perfil</span>
                  <span>{getProfileCompleteness()}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                    style={{ width: `${getProfileCompleteness()}%` }}
                  ></div>
                </div>
              </div>
              <CheckCircle className={`h-8 w-8 ${getProfileCompleteness() === 100 ? 'text-green-600' : 'text-gray-300'}`} />
            </div>
          </CardContent>
        </Card>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Información Personal */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="h-5 w-5 text-blue-600" />
                Información Personal
              </CardTitle>
              <CardDescription>
                Datos básicos de identificación
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="nombre">Nombre *</Label>
                  <Input
                    id="nombre"
                    value={formData.nombre}
                    onChange={(e) => handleInputChange('nombre', e.target.value)}
                    placeholder="Tu nombre"
                  />
                </div>
                <div>
                  <Label htmlFor="apellido_paterno">Apellido Paterno *</Label>
                  <Input
                    id="apellido_paterno"
                    value={formData.apellido_paterno}
                    onChange={(e) => handleInputChange('apellido_paterno', e.target.value)}
                    placeholder="Apellido paterno"
                  />
                </div>
              </div>

              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="apellido_materno">Apellido Materno</Label>
                  <Input
                    id="apellido_materno"
                    value={formData.apellido_materno}
                    onChange={(e) => handleInputChange('apellido_materno', e.target.value)}
                    placeholder="Apellido materno"
                  />
                </div>
                <div>
                  <Label htmlFor="fecha_nacimiento">Fecha de Nacimiento *</Label>
                  <Input
                    id="fecha_nacimiento"
                    type="date"
                    value={formData.fecha_nacimiento}
                    onChange={(e) => handleInputChange('fecha_nacimiento', e.target.value)}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Información de Contacto */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Phone className="h-5 w-5 text-green-600" />
                Información de Contacto
              </CardTitle>
              <CardDescription>
                Datos para comunicarnos contigo
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="telefono">Teléfono *</Label>
                  <Input
                    id="telefono"
                    value={formData.telefono}
                    onChange={(e) => handleInputChange('telefono', e.target.value)}
                    placeholder="+56 9 1234 5678"
                  />
                </div>
                <div>
                  <Label htmlFor="direccion">Dirección</Label>
                  <Input
                    id="direccion"
                    value={formData.direccion}
                    onChange={(e) => handleInputChange('direccion', e.target.value)}
                    placeholder="Tu dirección completa"
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Contacto de Emergencia */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Heart className="h-5 w-5 text-red-600" />
                Contacto de Emergencia
              </CardTitle>
              <CardDescription>
                Persona a contactar en caso de emergencia
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="contacto_emergencia_nombre">Nombre</Label>
                  <Input
                    id="contacto_emergencia_nombre"
                    value={formData.contacto_emergencia_nombre}
                    onChange={(e) => handleInputChange('contacto_emergencia_nombre', e.target.value)}
                    placeholder="Nombre completo"
                  />
                </div>
                <div>
                  <Label htmlFor="contacto_emergencia_telefono">Teléfono</Label>
                  <Input
                    id="contacto_emergencia_telefono"
                    value={formData.contacto_emergencia_telefono}
                    onChange={(e) => handleInputChange('contacto_emergencia_telefono', e.target.value)}
                    placeholder="+56 9 1234 5678"
                  />
                </div>
                <div>
                  <Label htmlFor="contacto_emergencia_relacion">Relación</Label>
                  <Select 
                    value={formData.contacto_emergencia_relacion} 
                    onValueChange={(value) => handleInputChange('contacto_emergencia_relacion', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Selecciona..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="padre">Padre</SelectItem>
                      <SelectItem value="madre">Madre</SelectItem>
                      <SelectItem value="hermano">Hermano/a</SelectItem>
                      <SelectItem value="hijo">Hijo/a</SelectItem>
                      <SelectItem value="conyugue">Cónyuge</SelectItem>
                      <SelectItem value="amigo">Amigo/a</SelectItem>
                      <SelectItem value="otro">Otro</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Información Médica Básica */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Heart className="h-5 w-5 text-purple-600" />
                Información Médica Básica
              </CardTitle>
              <CardDescription>
                Datos importantes para tu salud (opcional pero recomendado)
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="tipo_sangre">Tipo de Sangre</Label>
                  <Select 
                    value={formData.tipo_sangre} 
                    onValueChange={(value) => handleInputChange('tipo_sangre', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Selecciona..." />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="A+">A+</SelectItem>
                      <SelectItem value="A-">A-</SelectItem>
                      <SelectItem value="B+">B+</SelectItem>
                      <SelectItem value="B-">B-</SelectItem>
                      <SelectItem value="AB+">AB+</SelectItem>
                      <SelectItem value="AB-">AB-</SelectItem>
                      <SelectItem value="O+">O+</SelectItem>
                      <SelectItem value="O-">O-</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label htmlFor="altura">Altura (cm)</Label>
                  <Input
                    id="altura"
                    type="number"
                    value={formData.altura}
                    onChange={(e) => handleInputChange('altura', e.target.value)}
                    placeholder="170"
                  />
                </div>
                <div>
                  <Label htmlFor="peso">Peso (kg)</Label>
                  <Input
                    id="peso"
                    type="number"
                    value={formData.peso}
                    onChange={(e) => handleInputChange('peso', e.target.value)}
                    placeholder="70"
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="alergias">Alergias</Label>
                <Textarea
                  id="alergias"
                  value={formData.alergias}
                  onChange={(e) => handleInputChange('alergias', e.target.value)}
                  placeholder="Describe cualquier alergia conocida a medicamentos, alimentos, etc."
                  rows={2}
                />
              </div>

              <div>
                <Label htmlFor="condiciones_medicas">Condiciones Médicas</Label>
                <Textarea
                  id="condiciones_medicas"
                  value={formData.condiciones_medicas}
                  onChange={(e) => handleInputChange('condiciones_medicas', e.target.value)}
                  placeholder="Describe cualquier condición médica preexistente"
                  rows={2}
                />
              </div>
            </CardContent>
          </Card>

          {/* Alerta informativa */}
          <Alert>
            <CheckCircle className="h-4 w-4" />
            <AlertDescription>
              Tu información está protegida y solo será utilizada para mejorar tu atención médica. 
              Puedes modificar estos datos en cualquier momento.
            </AlertDescription>
          </Alert>

          {/* Botones de acción */}
          <div className="flex gap-4">
            <Link href="/bienvenida" className="flex-1">
              <Button variant="outline" type="button" className="w-full">
                Completar después
              </Button>
            </Link>
            <Button type="submit" disabled={processing} className="flex-1">
              {processing ? 'Guardando...' : 'Guardar perfil'}
            </Button>
          </div>
        </form>
      </div>
    </AppSidebarLayout>
  );
} 