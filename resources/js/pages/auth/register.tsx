import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    nombre: string;
    apellido_paterno: string;
    apellido_materno: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        nombre: '',
        apellido_paterno: '',
        apellido_materno: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Create an account" description="Enter your details below to create your account">
            <Head title="Register" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="nombre">Nombre</Label>
                        <Input
                            id="nombre"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="given-name"
                            value={data.nombre}
                            onChange={(e) => setData('nombre', e.target.value)}
                            disabled={processing}
                            placeholder="Juan"
                        />
                        <InputError message={errors.nombre} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="apellido_paterno">Apellido Paterno</Label>
                        <Input
                            id="apellido_paterno"
                            type="text"
                            required
                            tabIndex={2}
                            autoComplete="family-name"
                            value={data.apellido_paterno}
                            onChange={(e) => setData('apellido_paterno', e.target.value)}
                            disabled={processing}
                            placeholder="Pérez"
                        />
                        <InputError message={errors.apellido_paterno} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="apellido_materno">Apellido Materno</Label>
                        <Input
                            id="apellido_materno"
                            type="text"
                            tabIndex={3}
                            value={data.apellido_materno}
                            onChange={(e) => setData('apellido_materno', e.target.value)}
                            disabled={processing}
                            placeholder="González (opcional)"
                        />
                        <InputError message={errors.apellido_materno} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            tabIndex={4}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={5}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Password"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            tabIndex={8}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirm password"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={7} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Create account
                    </Button>
                </div>

                <div className="text-muted-foreground text-center text-sm">
                    Already have an account?{' '}
                    <TextLink href={route('login')} tabIndex={6}>
                        Log in
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
