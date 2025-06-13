import { Head, useForm } from '@inertiajs/react';
import { Activity, LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Card, CardHeader, CardContent, CardFooter } from '@/components/ui/card';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Bienvenido a MediTrack" description="Ingresa tus credenciales para acceder a tu cuenta">
            <Head title="Iniciar Sesión - MediTrack" />

            <Card className="w-full max-w-md mx-auto">
                <CardHeader>
                    <div className="flex flex-col items-center gap-2">
                        <Activity className="h-8 w-8 text-blue-600" />
                        <h2 className="text-2xl font-bold text-center">MediTrack</h2>
                        <p className="text-muted-foreground text-center">Inicia sesión en tu cuenta</p>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="flex flex-col gap-6" onSubmit={submit}>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Correo electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="correo@ejemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">Contraseña</Label>
                                    {canResetPassword && (
                                        <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                            ¿Olvidaste tu contraseña?
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Contraseña"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    checked={data.remember}
                                    onClick={() => setData('remember', !data.remember)}
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember">Recordarme</Label>
                            </div>

                            <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Iniciar Sesión
                            </Button>
                        </div>
                    </form>
                </CardContent>
                <CardFooter className="flex flex-col gap-4">
                    <div className="text-muted-foreground text-center text-sm">
                        ¿No tienes una cuenta?{' '}
                        <TextLink href={route('register')} tabIndex={5}>
                            Regístrate
                        </TextLink>
                    </div>
                    {status && <div className="text-center text-sm font-medium text-green-600">{status}</div>}
                </CardFooter>
            </Card>
        </AuthLayout>
    );
}
