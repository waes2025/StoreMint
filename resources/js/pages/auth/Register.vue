<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Create a customer account',
        description: 'Enter your details below to create your storefront buyer account',
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="route('register.store.form')"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="first_name">First name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        name="first_name"
                        required
                        autofocus
                        placeholder="John"
                    />
                    <InputError :message="errors.first_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="last_name">Last name</Label>
                    <Input
                        id="last_name"
                        type="text"
                        name="last_name"
                        required
                        placeholder="Doe"
                    />
                    <InputError :message="errors.last_name" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm Password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="processing"
                data-test="register-button"
            >
                <Spinner v-if="processing" />
                Create Account
            </Button>
        </div>

        <div class="text-center text-sm text-neutral-500">
            Already have an account?
            <TextLink :href="route('login').url" class="underline">
                Log in
            </TextLink>
        </div>
    </Form>
</template>
