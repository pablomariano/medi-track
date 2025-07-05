import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configuración del perfil',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    nombre: string;
    apellido_paterno: string;
    apellido_materno: string;
    email: string;
}

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth } = usePage<SharedData>().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        nombre: auth.user.nombre || '',
        apellido_paterno: auth.user.apellido_paterno || '',
        apellido_materno: auth.user.apellido_materno || '',
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Configuración del perfil" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Información de usuario" description="Actualiza tu nombre y correo electrónico" />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid grid-cols-3 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="nombre">Nombre</Label>
                                <Input
                                    id="nombre"
                                    className="mt-1 block w-full"
                                    value={data.nombre}
                                    onChange={(e) => setData('nombre', e.target.value)}
                                    required
                                    autoComplete="given-name"
                                    placeholder="Juan"
                                />
                                <InputError className="mt-2" message={errors.nombre} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="apellido_paterno">Apellido Paterno</Label>
                                <Input
                                    id="apellido_paterno"
                                    className="mt-1 block w-full"
                                    value={data.apellido_paterno}
                                    onChange={(e) => setData('apellido_paterno', e.target.value)}
                                    required
                                    autoComplete="family-name"
                                    placeholder="Pérez"
                                />
                                <InputError className="mt-2" message={errors.apellido_paterno} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="apellido_materno">Apellido Materno</Label>
                                <Input
                                    id="apellido_materno"
                                    className="mt-1 block w-full"
                                    value={data.apellido_materno}
                                    onChange={(e) => setData('apellido_materno', e.target.value)}
                                    placeholder="González"
                                />
                                <InputError className="mt-2" message={errors.apellido_materno} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Correo electrónico</Label>

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Correo electrónico"
                            />

                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="text-muted-foreground -mt-4 text-sm">
                                    Tu correo electrónico no está verificado.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Haz clic aquí para reenviar el correo de verificación.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Guardar</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Guardado</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
